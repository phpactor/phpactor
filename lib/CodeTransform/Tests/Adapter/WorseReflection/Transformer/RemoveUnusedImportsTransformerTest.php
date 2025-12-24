<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Transformer;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\RemoveUnusedImportsTransformer;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use function Amp\Promise\wait;

class RemoveUnusedImportsTransformerTest extends WorseTestCase
{
    #[DataProvider('provideRemoveUnusedImports')]
    public function testRemoveUnusedImport(string $example, string $expected): void
    {
        $source = SourceCode::fromString($example);
        $transformer = new RemoveUnusedImportsTransformer(
            $this->reflectorForWorkspace($example),
            new \Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider()
        );
        $transformed = wait($transformer->transform(SourceCode::fromString($source)));
        $this->assertEquals((string) $expected, (string) $transformed->apply($source));
    }

    /**
     * @return Generator<string,array{string,string}>
     */
    public static function provideRemoveUnusedImports(): Generator
    {
        yield 'It does nothing on source with no classes' => [
            <<<'EOT'
                <?php
                EOT
            ,
            <<<'EOT'
                <?php
                EOT

        ];
        yield 'It removes unused imports' => [
            <<<'EOT'
                <?php

                use Barfoo;

                new Foobar();
                EOT
            ,
            <<<'EOT'
                <?php


                new Foobar();
                EOT

        ];

        yield 'It does not remove aliased imports for existing' => [
            <<<'EOT'
                <?php

                use Barfoo as Foobar;

                new Foobar();
                EOT
            ,
            <<<'EOT'
                <?php

                use Barfoo as Foobar;

                new Foobar();
                EOT

        ];

        yield 'It removes unused imports with others' => [
            <<<'EOT'
                <?php

                use Foobar\Foobar;
                use Barfoo;
                use Symfony\Request;


                new Foobar();
                new Request();

                EOT
            ,
            <<<'EOT'
                <?php

                use Foobar\Foobar;
                use Symfony\Request;


                new Foobar();
                new Request();

                EOT

        ];

        yield 'It removes unused ugly inline' => [
            <<<'EOT'
                <?php

                use Foobar\Foobar; use Barfoo; use Symfony\Request;


                new Foobar();
                new Request();

                EOT
            ,
            <<<'EOT'
                <?php

                use Foobar\Foobar;  use Symfony\Request;


                new Foobar();
                new Request();

                EOT

        ];

        yield 'It removes names in compact use' => [
            <<<'EOT'
                <?php

                use Foobar\{Bag, Barfoo};
                use Symfony\Request;

                new Bag();
                new Request();

                EOT
            ,
            <<<'EOT'
                <?php

                use Foobar\{Bag};
                use Symfony\Request;

                new Bag();
                new Request();

                EOT
        ];

        // this is a workaround to avoid overlapping text edits
        yield 'It only fixes one missing import per run' => [
            <<<'EOT'
                <?php

                use Foobar\{Bag, Barfoo, Barrrr, Request};

                new Bag();
                new Request();

                EOT
            ,
            <<<'EOT'
                <?php

                use Foobar\{Bag, Barrrr, Request};

                new Bag();
                new Request();

                EOT
        ];
    }
}
