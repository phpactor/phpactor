<?php

namespace Phpactor\Indexer\Tests\Adapter;

use Closure;
use Generator;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use function Safe\file_get_contents;

abstract class IndexBuilderTestCase extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest(file_get_contents(__DIR__ . '/Manifest/buildIndex.php.test'));
    }

    /**
     * @dataProvider provideIndexesClassLike
     * @dataProvider provideIndexesReferences
     */
    public function testIndexClass(string $source, string $name, Closure $assertions): void
    {
        $this->workspace()->loadManifest($source);
        $index = $this->buildIndex();
        $class = $this->indexQuery($index)->class()->get($name);

        self::assertNotNull($class, 'Class was found');

        $assertions($class);
    }

    /**
     * @return Generator<string, array>
     */
    public function provideIndexesClassLike(): Generator
    {
        yield 'class' => [
            "// File: project/test.php\n<?php class ThisClass {}",
            'ThisClass',
            function (ClassRecord $record): void {
                self::assertInstanceOf(ClassRecord::class, $record);
                self::assertEquals($this->workspace()->path('project/test.php'), $record->filePath());
                self::assertEquals('ThisClass', $record->fqn());
                self::assertEquals(6, $record->start()->toInt());
                self::assertEquals(ClassRecord::TYPE_CLASS, $record->type());
            }
        ];

        yield 'namespaced class' => [
            "// File: project/test.php\n<?php namespace Foobar { class ThisClass {} }",
            'Foobar\\ThisClass',
            function (ClassRecord $record): void {
                self::assertEquals('Foobar\\ThisClass', $record->fqn());
            }
        ];

        yield 'extended class has implementations' => [
            "// File: project/test.php\n<?php class Foobar {} class Barfoo extends Foobar {}",
            'Foobar',
            function (ClassRecord $record): void {
                self::assertCount(1, $record->implementations());
            }
        ];

        yield 'namespaced extended abstract class has implementations' => [
            "// File: project/test.php\n<?php namespace Foobar; abstract class Foobar {} class Barfoo extends Foobar {}",
            'Foobar\Foobar',
            function (ClassRecord $record): void {
                self::assertCount(1, $record->implementations());
            }
        ];

        yield 'interface referenced by alias from another namespace' => [
            <<<'EOT'
                // File: project/test.php
                <?php namespace Foobar; interface Barfoo {}
                // File: project/test2.php
                <?php namespace Barfoo;
                use Foobar\Barfoo as BarBar;
                class Barfoo implements BarBar {}
                EOT
            , 'Foobar\Barfoo',
            function (ClassRecord $record): void {
                self::assertCount(1, $record->implementations());
            }
        ];

        yield 'class implements' => [
            "// File: project/test.php\n<?php class Foobar {} class Barfoo extends Foobar {}",
            'Barfoo',
            function (ClassRecord $record): void {
                self::assertCount(1, $record->implements());
            }
        ];

        yield 'interface has class implementation' => [
            "// File: project/test.php\n<?php interface Foobar {} class ThisClass implements Foobar {}",
            'Foobar',
            function (ClassRecord $record): void {
                self::assertCount(1, $record->implementations());
            }
        ];

        yield 'namespaced interface has class implementation' => [
            "// File: project/test.php\n<?php namespace Foobar; interface Foobar {} class ThisClass implements Foobar {}",
            'Foobar\Foobar',
            function (ClassRecord $record): void {
                self::assertCount(1, $record->implementations());
            }
        ];

        yield 'interface implements' => [
            "// File: project/test.php\n<?php interface Foobar {} interface Barfoo extends Foobar {}",
            'Foobar',
            function (ClassRecord $record): void {
                self::assertCount(1, $record->implementations());
            }
        ];


        yield 'namespaced interface implements' => [
            "// File: project/test.php\n<?php namespace Foobar; interface Foobar {} interface Barfoo extends Foobar {}",
            'Foobar\Foobar',
            function (ClassRecord $record): void {
                self::assertCount(1, $record->implementations());
            }
        ];

        yield 'interface has class implementations' => [
            "// File: project/test.php\n<?php interface Foobar {} class ThisClass implements Foobar {} class ThatClass implements Foobar {}",
            'Foobar',
            function (ClassRecord $record): void {
                self::assertCount(2, $record->implementations());
            }
        ];

        yield 'interface' => [
            "// File: project/test.php\n<?php interface ThisInterface {}",
            'ThisInterface',
            function (ClassRecord $record): void {
                self::assertInstanceOf(ClassRecord::class, $record);
                self::assertEquals($this->workspace()->path('project/test.php'), $record->filePath());
                self::assertEquals('ThisInterface', $record->fqn());
                self::assertEquals(6, $record->start()->toInt());
                self::assertEquals(ClassRecord::TYPE_INTERFACE, $record->type());
            }
        ];

        yield 'namespaced interface' => [
            "// File: project/test.php\n<?php namespace Foobar {interface ThisInterface {}}",
            'Foobar\\ThisInterface',
            function (ClassRecord $record): void {
                self::assertEquals('Foobar\\ThisInterface', $record->fqn());
            }
        ];

        yield 'trait' => [
            "// File: project/test.php\n<?php trait ThisTrait {}",
            'ThisTrait',
            function (ClassRecord $record): void {
                self::assertInstanceOf(ClassRecord::class, $record);
                self::assertEquals($this->workspace()->path('project/test.php'), $record->filePath());
                self::assertEquals('ThisTrait', $record->fqn());
                self::assertEquals(6, $record->start()->toInt());
                self::assertEquals(ClassRecord::TYPE_TRAIT, $record->type());
            }
        ];

        yield 'class uses trait' => [
            <<<'EOT'
                // File: project/test1.php
                <?php
                namespace Foobar;

                trait ThisIsTrait {}
                // File: project/test2.php
                <?php
                namespace Barfoo;

                use Foobar\ThisIsTrait;

                class Hoo
                {
                    use ThisIsTrait;
                }
                EOT
            , 'Foobar\ThisIsTrait',
            function (ClassRecord $record): void {
                self::assertCount(1, $record->implementations());
            }
        ];

        if (version_compare(PHP_VERSION, '8.1', '>=')) {
            yield 'enum' => [
                "// File: project/test.php\n<?php enum SomeEnum {}",
                'SomeEnum',
                function (ClassRecord $record): void {
                    self::assertInstanceOf(ClassRecord::class, $record);
                    self::assertEquals($this->workspace()->path('project/test.php'), $record->filePath());
                    self::assertEquals('SomeEnum', $record->fqn());
                    self::assertEquals(6, $record->start()->toInt());
                    self::assertEquals(ClassRecord::TYPE_ENUM, $record->type());
                }
            ];
        }
    }

    /**
     * @return Generator<string, array>
     */
    public function provideIndexesReferences(): Generator
    {
        yield 'single reference' => [
            <<<'EOT'
                // File: project/test1.php
                <?php
                class Foobar
                {
                }
                // File: project/test2.php
                <?php
                new Foobar();
                EOT
            , 'Foobar',
            function (ClassRecord $record): void {
                self::assertCount(1, $record->references());
            }
        ];

        yield 'multiple references' => [
            <<<'EOT'
                // File: project/test1.php
                <?php
                class Foobar
                {
                }

                // File: project/test2.php
                <?php
                new Foobar();
                new Foobar();
                new Foobar();

                // File: project/test3.php
                <?php
                new Foobar();
                new Foobar();
                new Foobar();
                EOT
            , 'Foobar',
            function (ClassRecord $record): void {
                // there is one file reference per class
                self::assertCount(2, $record->references());
            }
        ];

        yield 'incoming namespaced references' => [
            <<<'EOT'
                // File: project/test1.php
                <?php

                namespace Foobar;

                class Foobar
                {
                }

                // File: project/test2.php
                <?php

                new Foobar\Foobar();

                // File: project/test3.php
                <?php

                use Foobar\Foobar;
                new Foobar();
                EOT
            , 'Foobar\Foobar',
            function (ClassRecord $record): void {
                // there is one file reference per class
                self::assertCount(2, $record->references());
            }
        ];

        yield 'outgoing namespaced references' => [
            <<<'EOT'
                // File: project/test1.php
                <?php

                namespace Foobar;

                use Test\Something;

                class Foobar
                {
                    public function something()
                    {
                        new Something();
                    }
                }

                // File: project/test2.php
                <?php

                namespace Test;

                class Something
                {
                }

                EOT
            , 'Test\Something',
            function (ClassRecord $record): void {
                // there is one file reference per class
                self::assertCount(1, $record->references());
            }
        ];

        yield 'static call reference' => [
            <<<'EOT'
                // File: project/test1.php
                <?php
                class Foobar
                {
                }

                // File: project/test2.php
                <?php
                Foobar::foobar();
                EOT
            , 'Foobar',
            function (ClassRecord $record): void {
                // there is one file reference per class
                self::assertCount(1, $record->references());
            }
        ];
    }

    /**
     * @dataProvider provideIndexesFunctions
     */
    public function testIndexFunction(string $source, string $name, Closure $assertions): void
    {
        $this->workspace()->loadManifest($source);
        $index = $this->buildIndex();
        $class = $this->indexQuery($index)->function()->get(
            $name
        );

        self::assertNotNull($class, 'Function was found');

        $assertions($class);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideIndexesFunctions(): Generator
    {
        yield 'function' => [
            <<<'EOT'
                // File: project/test1.php
                <?php function foobar();

                // File: project/test2.php
                <?php
                foobar();
                EOT
            , 'foobar',
            function (FunctionRecord $record): void {
                // there is one file reference per class
                self::assertCount(1, $record->references());
            }
        ];

        yield 'namespaced function' => [
            <<<'EOT'
                // File: project/test1.php
                <?php
                namespace Barfoos;
                foobar();

                // File: project/test2.php
                <?php
                Barfoos\foobar();
                EOT
            , 'Barfoos\foobar',
            function (FunctionRecord $record): void {
                // there is one file reference per class
                self::assertCount(1, $record->references());
                self::assertNull($record->filePath());
            }
        ];

        yield 'declaration is indexed' => [
            <<<'EOT'
                // File: project/test1.php
                <?php
                namespace Barfoos;

                function foobar() {};

                // File: project/test2.php
                <?php
                Barfoos\foobar();
                EOT
            , 'Barfoos\foobar',
            function (FunctionRecord $record): void {
                self::assertCount(1, $record->references());
                self::assertEquals($this->workspace()->path('project/test1.php'), $record->filePath());
            }
        ];
    }

    public function testInterfaceImplementations(): void
    {
        $index = $this->buildIndex();

        $references = $this->indexQuery($index)->class()->implementing('Index');

        self::assertCount(2, $references);
    }

    public function testFunctions(): void
    {
        $index = $this->buildIndex();

        $function = $this->indexQuery($index)->function()->get(
            'Hello\world'
        );

        self::assertInstanceOf(Record::class, $function);
    }

    public function testChildClassImplementations(): void
    {
        $index = $this->buildIndex();

        $references = $this->indexQuery($index)->class()->implementing(
            'AbstractClass'
        );

        self::assertCount(2, $references);
    }

    public function testPicksUpNewFiles(): void
    {
        $index = $this->buildIndex();

        $references = $this->indexQuery($index)->class()->implementing(
            'AbstractClass'
        );
        self::assertCount(2, $references);

        $this->workspace()->put(
            'project/Foobar.php',
            <<<'EOT'
                <?php

                class Foobar extends AbstractClass
                {
                }
                EOT
        );

        $this->buildIndex($index);

        $references = $this->indexQuery($index)->class()->implementing(
            'AbstractClass'
        );

        self::assertCount(3, $references);
    }

    public function testRemovesExistingImplementationReferences(): void
    {
        $index = $this->buildIndex();

        $references = $this->indexQuery($index)->class()->implementing(
            'AbstractClass'
        );
        self::assertCount(2, $references);


        $this->workspace()->put(
            'project/AbstractClassImplementation1.php',
            <<<'EOT'
                <?php

                class AbstractClassImplementation1
                {
                }
                EOT
        );

        $index = $this->buildIndex($index);

        $references = $this->indexQuery($index)->class()->implementing(
            'AbstractClass'
        );

        self::assertCount(1, $references);
    }

    public function testDoesNotRemoveExisting(): void
    {
        $this->workspace()->put(
            'project/0000.php',
            <<<'EOT'
                <?php

                class Foobar extends AbstractClass
                {
                }
                EOT
        );
        $this->workspace()->put(
            'project/ZZZZ.php',
            <<<'EOT'
                <?php

                class ZedFoobar extends AbstractClass
                {
                }
                EOT
        );

        $index = $this->buildIndex();

        $references = $this->indexQuery($index)->class()->implementing(
            'AbstractClass'
        );
        self::assertCount(4, $references);

        $this->workspace()->put(
            'project/0000.php',
            <<<'EOT'
                <?php

                class Foobar
                {
                }
                EOT
        );
        usleep(50);

        $index = $this->buildIndex($index);

        $references = $this->indexQuery($index)->class()->implementing(
            'AbstractClass'
        );

        self::assertCount(3, $references);
    }
}
