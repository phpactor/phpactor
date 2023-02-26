<?php

namespace Phpactor\ClassMover\Tests\Adapter\TolerantParser;

use Microsoft\PhpParser\Parser;
use Phpactor\ClassMover\Adapter\TolerantParser\TolerantClassFinder;
use PHPUnit\Framework\TestCase;
use Phpactor\ClassMover\Adapter\TolerantParser\TolerantClassReplacer;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TolerantUpdater;
use Phpactor\CodeBuilder\Adapter\Twig\TwigRenderer;
use Phpactor\TextDocument\TextDocumentBuilder;

class TolerantClassReplacerTest extends TestCase
{
    /**
     * @testdox It finds all class references.
     * @dataProvider provideTestFind
     */
    public function testFind(string $fileName, string $classFqn, string $replaceWithFqn, string $expectedSource): void
    {
        $parser = new Parser();
        $tolerantRefFinder = new TolerantClassFinder($parser);
        $source = TextDocumentBuilder::fromUri(__DIR__ . '/examples/' . $fileName)->build();
        $originalName = FullyQualifiedName::fromString($classFqn);

        $names = $tolerantRefFinder->findIn($source)->filterForName($originalName);

        $updater = new TolerantUpdater(new TwigRenderer());

        $replacer = new TolerantClassReplacer($updater);
        $edits = $replacer->replaceReferences($source, $names, $originalName, FullyQualifiedName::fromString($replaceWithFqn));
        $stripEmptyLines = function (string $source) {
            return implode("\n", array_filter(explode("\n", $source), function (string $line) {
                return $line !== '';
            }));
        };
        self::assertStringContainsString($stripEmptyLines($expectedSource), $stripEmptyLines($edits->apply($source->__toString())));
    }

    /**
     * @return array<string, array<string>>
     */
    public function provideTestFind(): array
    {
        return [
            'Change references of moved class' => [
                'Example1.php',
                'Acme\\Foobar\\Warble',
                'BarBar\\Hello',
                <<<'EOT'
                    <?php
                    namespace Acme;
                    use BarBar\Hello;
                    use Acme\Foobar\Barfoo;
                    use Acme\Barfoo as ZedZed;

                    class Hello
                    {
                        public function something(): void
                        {
                            $foo = new Hello();
                    EOT
            ],
            'Changes class name of moved class' => [
                'Example1.php',
                'Acme\\Hello',
                'Acme\\Definee',
                <<<'EOT'
                    <?php
                    namespace Acme;

                    use Acme\Foobar\Warble;
                    use Acme\Foobar\Barfoo;
                    use Acme\Barfoo as ZedZed;

                    class Definee
                    EOT
            ],
            'Change namespace of moved class 1' => [
                'Example1.php',
                'Acme\\Hello',
                'Acme\\Definee\\Foobar',
                <<<'EOT'
                    namespace Acme\Definee;

                    use Acme\Foobar\Warble;
                    use Acme\Foobar\Barfoo;
                    use Acme\Barfoo as ZedZed;

                    class Foobar
                    EOT
            ],
            'Change namespace of class which has same namespace as current file' => [
                'Example2.php',
                'Acme\\Barfoo',
                'Acme\\Definee\\Barfoo',
                <<<'EOT'
                    <?php
                    namespace Acme;

                    use Acme\Definee\Barfoo;

                    class Hello
                    {
                        public function something(): void
                        {
                            Barfoo::foobar();
                        }
                    }
                    EOT
            ],
            'Change namespace of long class' => [
                'Example3.php',
                'Acme\\ClassMover\\RefFinder\\RefFinder\\TolerantRefFinder',
                'Acme\\ClassMover\\Bridge\\Microsoft\\TolerantParser\\TolerantRefFinder',
                <<<'EOT'
                    use Acme\ClassMover\Bridge\Microsoft\TolerantParser\TolerantRefFinder;
                    EOT
            ],
            'Change namespace of interface' => [
                'Example5.php',
                'Phpactor\ClassMover\Tests\Adapter\TolerantParser\Example5Interface',
                'Phpactor\ClassMover\Tests\Adapter\TolerantParser\BarBar\FoobarInterface',
                <<<'EOT'
                    <?php
                    namespace Phpactor\ClassMover\Tests\Adapter\TolerantParser\BarBar;
                    EOT
            ],
            'Change namespace of trait' => [
                'Example6.php',
                'Phpactor\ClassMover\Tests\Adapter\TolerantParser\ExampleTrait',
                'Phpactor\ClassMover\Tests\Adapter\TolerantParser\BarBar\FoobarTrait',
                <<<'EOT'
                    namespace Phpactor\ClassMover\Tests\Adapter\TolerantParser\BarBar;
                    EOT
            ],
            'Change name of class expansion' => [
                'Example4.php',
                'Acme\\ClassMover\\RefFinder\\RefFinder\\TolerantRefFinder',
                'Acme\\ClassMover\\RefFinder\\RefFinder\\Foobar',
                <<<'EOT'
                    <?php
                    namespace Acme;
                    use Acme\ClassMover\RefFinder\RefFinder\Foobar;
                    class Hello
                    {
                        public function something(): void
                        {
                            Foobar::class;
                        }
                    }
                    EOT
            ],
            'Class which includes use statement for itself' => [
                'Example7.php',
                'Phpactor\ClassMover\Tests\Adapter\TolerantParser\Example7',
                'Phpactor\ClassMover\Tests\Adapter\TolerantParser\Example8',
                <<<'EOT'
                    class Example8
                    EOT
            ],
            'Self class with no namespace to a namespace' => [
                'Example8.php',
                'ClassOne',
                'Phpactor\ClassMover\Example8',
                <<<'EOT'
                    <?php
                    namespace Phpactor\ClassMover;


                    class Example8
                    {
                        public function build()
                        {
                            return new self();
                        }
                    }
                    EOT
            ],
            'Class with no namespace to a namespace' => [
                'Example9.php',
                'Example',
                'Phpactor\ClassMover\Example',
                <<<'EOT'
                    <?php

                    use Phpactor\ClassMover\Example;

                    class ClassOne
                    {
                        public function build(): Example
                    EOT
            ],
            'Aliased class' => [
                'Example10.php',
                'Foobar\Example',
                'Phpactor\ClassMover\Example',
                <<<'EOT'
                    <?php

                    use Phpactor\ClassMover\Example as BadExample;

                    class ClassOne
                    {
                        public function build(): BadExample
                    EOT
            ],
            'Aliased class named the same' => [
                'Example11.php',
                'Foobar\Example',
                'Phpactor\ClassMover\Example',
                <<<'EOT'
                    <?php

                    use Phpactor\ClassMover\Example as BadExample;
                    class Example extends BadExample
                    {
                    }
                    EOT
            ],
        ];
    }
}
