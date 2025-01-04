<?php

namespace Phpactor\Indexer\Tests\Adapter\ReferenceFinder;

use Generator;
use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedReferenceFinder;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class IndexedReferenceFinderTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    /**
     * @dataProvider provideClasses
     * @dataProvider provideTraits
     * @dataProvider provideFunctions
     * @dataProvider provideMembers
     * @dataProvider provideUnknown
     */
    public function testFinder(string $manifest, int $expectedConfirmed, ?int $expectedTotal = 0): void
    {
        $expectedTotal = $expectedTotal ?: $expectedConfirmed;
        $this->workspace()->reset();
        $this->workspace()->loadManifest($manifest);
        [ $source, $offset ] = ExtractOffset::fromSource($this->workspace()->getContents('project/subject.php'));
        $this->workspace()->put('project/subject.php', $source);

        $this->indexAgent()->indexer()->getJob()->run();

        $referenceFinder = new IndexedReferenceFinder(
            $this->indexAgent()->query(),
            $this->createReflector(),
        );

        $locations = $referenceFinder->findReferences(
            TextDocumentBuilder::create($source)->build(),
            ByteOffset::fromInt((int)$offset)
        );

        $locations = iterator_to_array($locations);

        $sureLocations = array_filter($locations, function (PotentialLocation $location) {
            return $location->isSurely();
        });

        self::assertCount($expectedConfirmed, $sureLocations, 'Total confirmed');
        self::assertCount($expectedTotal, $locations, 'Total expected');
    }

    /**
     * @return Generator<mixed>
     */
    public function provideClasses(): Generator
    {
        yield 'single class' => [
            <<<'EOT'
                // File: project/subject.php
                <?php new Fo<>o();
                EOT
        ,
            1
        ];

        yield 'class references' => [
            <<<'EOT'
                // File: project/subject.php
                <?php class Fo<>o {}
                // File: project/class1.php
                <?php

                new Foo();
                // File: project/class2.php
                <?php

                Foo::bar();
                EOT
        ,
            2
        ];

        yield 'class deep references' => [
            <<<'EOT'
                // File: project/subject.php
                <?php class Fo<>o {}
                // File: project/class1.php
                <?php

                class Bar extends Foo {}
                // File: project/class2.php
                <?php

                Bar::bar();
                EOT
        ,
            2
        ];

        yield 'class deep references if implementation implements some additional interface' => [
            <<<'EOT'
                // File: project/subject.php
                <?php interface Watche<>r {}
                // File: project/AnotherInterface.php
                <?php interface AnotherInterface {}
                // File: project/TestWatcher.php
                <?php class TestWatcher implements Watcher, AnotherInterface {}
                // File: project/class2.php
                <?php

                function bar(AnotherInterface $ai);
                EOT
        ,
            1
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideTraits(): Generator
    {
        yield 'single trait' => [
            <<<'EOT'
                // File: project/trait.php
                <?php trait Bar { };

                // File: project/subject.php
                <?php class Foo { use Ba<>r; };
                EOT
            ,
            1
        ];

        yield 'implementation' => [
            <<<'EOT'
                // File: project/trait.php
                <?php trait Bar { };

                // File: project/subject.php
                <?php class Foo { use Ba<>r; };

                // File: project/other.php
                <?php
                $b = new Foo();
                EOT
            ,
            2
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideFunctions(): Generator
    {
        yield 'function references' => [
            <<<'EOT'
                // File: project/subject.php
                <?php function he<>llo_world() {}
                // File: project/class1.php
                <?php

                hello_world();
                // File: project/class2.php
                <?php

                hello_world();
                EOT
        ,
            2
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideMembers(): Generator
    {
        yield 'show new object expressions when finding references on __construct except for superclass' => [
            <<<'EOT'
                // File: project/Bar.php
                <?php class Bar { public function __construct() {} }
                // File: project/subject.php
                <?php class SubBar extends Bar { public function __c<>onstruct() {} }
                // File: project/subject.php
                <?php class Other extends Bar { public function __construct() {} }

                // File: project/class1.php
                <?php
                new Bar(); // Should not be found, too low
                new SubBar(); // Should be found
                new Other(); // Should not be found, it's overridden with its own constructor
                EOT
                ,
                1
        ];

        yield 'show new object expressions when finding references on __construct except for superclass 2' => [
            <<<'EOT'
                // File: project/Bar.php
                <?php class Bar { public function __construct() {} }
                // File: project/subject.php
                <?php class SubBar extends Bar { public function __c<>onstruct() {} }
                // File: project/subject.php
                <?php class Other extends SubBar { }

                // File: project/class1.php
                <?php
                new Bar(); // Should not be found
                new SubBar(); // Should be found
                new Other(); // Should be found, since it's not overridden
                EOT
                ,
                2
        ];

        yield 'static members' => [
            <<<'EOT'
                // File: project/subject.php
                <?php Foobar::b<>ar() {}
                // File: project/class1.php
                <?php Foobar::bar() {}
                // File: project/class2.php
                <?php
                <?php Foobar::bar() {}
                EOT
        ,
            3
        ];

        yield 'namespaced static members' => [
            <<<'EOT'
                // File: project/subject.php
                <?php namespace Bar; Foobar::b<>ar() {}
                // File: project/class1.php
                <?php Bar\Foobar::bar() {}
                // File: project/class2.php
                <?php
                <?php use Bar\Foobar; Foobar::bar() {}
                EOT
        ,
            3
        ];

        yield 'instance members' => [
            <<<'EOT'
                // File: project/subject.php
                <?php namespace Bar; $foo = new Foobar(); $foo->b<>ar();

                // File: project/class1.php
                <?php namespace Bar; class Foobar { public function bar() {}}

                EOT
        ,
            1, 1
        ];

        yield 'deep members' => [
            <<<'EOT'
                // File: project/subject.php
                <?php namespace Bar; $foo = new Foobar(); $foo->b<>ar();

                // File: project/subject1.php
                <?php namespace Bar; $bar = new Barfoo(); $bar->bar();

                // File: project/class1.php
                <?php namespace Bar; class Barfoo { public function bar() {}}

                // File: project/class2.php
                <?php namespace Bar; class Foobar extends Barfoo { public function bar() {}}

                EOT
        ,
            1, 2
        ];

        yield 'deep members no override' => [
            <<<'EOT'
                // File: project/subject.php
                <?php $foo = new Foobar(); $foo->met<>hod();

                // File: project/subject1.php
                <?php $bar = new ParentalClass(); $bar->method();

                // File: project/class1.php
                <?php class ParentalClass { public function method() {}}

                // File: project/class2.php
                <?php class Foobar extends ParentalClass { }
                EOT
        ,
            2, 4 // total number is multiple due to implementation recursion
        ];

        yield 'static properties' => [
            <<<'EOT'
                // File: project/foobar.php
                <?php class Foobar { public static $staticProp; function f(){ self::$staticProp = 5; } }

                // File: project/subject.php
                <?php Foobar::$st<>aticProp = 5;

                // File: project/class1.php
                <?php var $b = Foobar::$staticProp;
                EOT
        ,
            3
        ];

        yield 'show new object expressions when finding references on __construct' => [
            <<<'EOT'
                // File: project/foobar.php
                <?php Foobar::bar();

                // File: project/subject.php
                <?php class Foobar { public function __c<>onstruct() {} public static function bar() {} }

                // File: project/class1.php
                <?php new Foobar();
                EOT
        ,
            1
        ];

        yield 'show new object expressions when finding references on __construct 2' => [
            <<<'EOT'
                // File: project/foobar.php
                <?php Foobar::bar();

                // File: project/subject.php
                <?php class Foobar { public function __c<>onstruct() {} public static function bar() {} }

                // File: project/class1.php
                <?php new Foobar(); new Barfoo();
                EOT
        ,
            1
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideUnknown(): Generator
    {
        yield 'variable' => [
            <<<'EOT'
                // File: project/subject.php
                <?php $a<>sd;
                EOT
        ,
            0
        ];
    }
}
