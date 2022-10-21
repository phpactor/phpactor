<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseSimplifyClassName;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Domain\SourceCode;

class SimplifyClassNameTest extends WorseTestCase
{
    /**
     * @dataProvider dataFQNToImport
     */
    public function testFQNToImport(string $test): void
    {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);

        $simplifyClassName = new WorseSimplifyClassName(
            $this->reflectorForWorkspace($source),
            new WorseBuilderFactory($this->reflectorForWorkspace($source))
        );

        $textDocumentEdits = $simplifyClassName->getTextEdits(
            SourceCode::fromStringAndPath($source, 'file:///source'),
            $offset
        );
        $sourceCode = SourceCode::fromStringAndPath($source, 'file:///source');
        $transformed = SourceCode::fromStringAndPath(
            (string) $textDocumentEdits->textEdits()->apply($sourceCode),
            $textDocumentEdits->uri()->path()
        );

        $this->assertEquals(trim($expected), trim($transformed));
    }

    /**
     * @return array<string,array<string>>
     */
    public function dataFQNToImport(): array
    {
        return [
            'in an expression' => [ 'fqnToImport1.test' ],
            'in a parameter' => [ 'fqnToImport2.test' ],
        ];
    }
}
