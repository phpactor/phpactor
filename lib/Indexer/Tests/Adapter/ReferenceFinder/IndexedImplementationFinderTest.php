<?php

namespace Phpactor\Indexer\Tests\Adapter\ReferenceFinder;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedImplementationFinder;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class IndexedImplementationFinderTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    #[DataProvider('provideClassLikes')]
    #[DataProvider('provideClassMembers')]
    public function testFinder(string $manifest, int $expectedLocationCount): void
    {
        $this->workspace()->loadManifest($manifest);
        [ $source, $offset ] = ExtractOffset::fromSource($this->workspace()->getContents('project/subject.php'));
        $this->workspace()->put('project/subject.php', $source);

        $index = $this->buildIndex();

        $implementationFinder = new IndexedImplementationFinder(
            $this->indexQuery($index),
            $this->createReflector()
        );

        $locations = $implementationFinder->findImplementations(
            TextDocumentBuilder::create($source)->build(),
            ByteOffset::fromInt((int)$offset)
        );

        self::assertCount($expectedLocationCount, $locations);
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideClassLikes(): Generator
    {
        yield 'interface implementations' => [
            <<<'EOT'
                // File: project/subject.php
                <?php interface Fo<>oInterface {}
                // File: project/class.php
                <?php

                class Foobar implements FooInterface {}
                class Barfoo implements FooInterface {}
                EOT
        ,
            2
        ];

        yield 'class implementations' => [
            <<<'EOT'
                // File: project/subject.php
                <?php class Fo<>o {}
                // File: project/class.php
                <?php

                class Foobar extends Foo {}
                class Barfoo extends Foo {}
                EOT
        ,
            2
        ];

        yield 'abstract class implementations' => [
            <<<'EOT'
                // File: project/subject.php
                <?php abstract class Fo<>o {}
                // File: project/class.php
                <?php

                class Foobar extends Foo {}
                class Barfoo extends Foo {}
                EOT
        ,
            2
        ];

        yield 'implementations of abstract class implementation' => [
            <<<'EOT'
                // File: project/subject.php
                <?php abstract class Fo<>o {}
                // File: project/class.php
                <?php

                class Foobar extends Foo {}
                class Barfoo extends Foo {}
                class Carfoo extends Barfoo {}
                EOT
        ,
            3
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideClassMembers(): Generator
    {
        yield 'none' => [
            <<<'EOT'
                // File: project/subject.php
                <?php interface FooInterface {
                   public function doT<>his();
                }
                EOT
        ,
            0
        ];

        yield 'interface member' => [
            <<<'EOT'
                // File: project/subject.php
                <?php interface FooInterface {
                   public function doT<>his();
                }
                // File: project/class.php
                <?php

                class Foobar implements FooInterface {
                    public function doThis();
                }
                class Barfoo implements FooInterface {
                    public function doThis();
                }
                EOT
        ,
            2
        ];

        yield 'does not count abstract class member' => [
            <<<'EOT'
                // File: project/subject.php
                <?php abstract class Foo {
                   abstract public function doT<>his();
                }
                // File: project/class.php
                <?php

                class Foobar extends Foo {
                    public function doThis();
                }
                EOT
        ,
            1
        ];

        yield 'member implementations of abstract class implementation' => [
            <<<'EOT'
                // File: project/subject.php
                <?php $foo = new Foo();
                   $foo->d<>oThis();
                }
                // File: project/class.php
                <?php
                class Bar {
                    public function doThis();
                }

                // File: project/foo.php
                <?php
                class Foo extends Bar {
                }
                EOT
        ,
            1
        ];
    }
}
