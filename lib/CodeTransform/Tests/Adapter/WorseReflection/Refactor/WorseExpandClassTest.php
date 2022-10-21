<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseExpandClass;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Domain\SourceCode;

class WorseExpandClassTest extends WorseTestCase
{
    /**
     * @dataProvider provideExpandClass
     */
    public function testExpandingClass(string $test): void
    {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);

        $extractConstant = new WorseExpandClass(
            $this->reflectorForWorkspace($source)
        );
        $textDocumentEdits = $extractConstant->getTextEdits(
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
    public function provideExpandClass(): array
    {
        return [
            'in an expression' => [ 'expandClass1.test' ],
            'in a parameter' => [ 'expandClass2.test' ],
        ];
    }
}
