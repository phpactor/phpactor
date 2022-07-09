<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Transformer;

use Generator;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\RemoveUnusedImportsTransformer;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\AddMissingProperties;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;

class RemoveUnusedImportsTransformerTest extends WorseTestCase
{
    /**
     * @dataProvider provideRemoveUnusedImports
     */
    public function testRemoveUnusedImport(string $example, string $expected): void
    {
        $source = SourceCode::fromString($example);
        $transformer = new RemoveUnusedImportsTransformer(
            $this->reflectorForWorkspace($example),
            new Parser()
        );
        $transformed = $transformer->transform(SourceCode::fromString($source));
        $this->assertEquals((string) $expected, (string) $transformed->apply($source));
    }

    /**
     * @return Generator<string,array{string,string}>
     */
    public function provideRemoveUnusedImports(): Generator
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

        yield 'It compact use' => [
            <<<'EOT'
                <?php

                use Foobar\{Foobar, Barfoo};
                use Symfony\Request;


                new Foobar();
                new Request();

                EOT
            ,
            <<<'EOT'
                <?php

                use Foobar\{Foobar};
                use Symfony\Request;


                new Foobar();
                new Request();

                EOT

        ];
    }
}
