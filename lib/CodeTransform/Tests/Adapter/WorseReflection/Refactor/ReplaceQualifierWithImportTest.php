<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Generator;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseReplaceQualifierWithImport;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Domain\SourceCode;

class ReplaceQualifierWithImportTest extends WorseTestCase
{
    /**
     * @dataProvider dataFQNToImport
     */
    public function testFQNToImport(string $test): void
    {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);

        $replaceQualifierWithImport = new WorseReplaceQualifierWithImport(
            $this->reflectorForWorkspace($source),
            new WorseBuilderFactory($this->reflectorForWorkspace($source)),
            $this->updater()
        );

        $textDocumentEdits = $replaceQualifierWithImport->getTextEdits(
            SourceCode::fromStringAndPath($source, 'file:///source'),
            $offset
        );
        $sourceCode = SourceCode::fromStringAndPath($source, 'file:///source');
        $transformed = SourceCode::fromStringAndPath(
            (string) $textDocumentEdits->textEdits()->apply($sourceCode),
            $textDocumentEdits->uri()->path()
        );

        self::assertEquals(trim($expected), trim($transformed));
    }

    /**
     * @return Generator<string,array<string>>
     */
    public function dataFQNToImport(): Generator
    {
        yield 'in an expression' => [ 'replaceQualifierWithImport1.test' ];
        yield 'in a parameter' => [ 'replaceQualifierWithImport2.test' ];
    }
}
