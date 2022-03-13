<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseExtractConstant;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Exception\TransformException;

class WorseExtractConstantTest extends WorseTestCase
{
    /**
     * @dataProvider provideExtractMethod
     */
    public function testExtractConstant(string $test, $name): void
    {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);

        $extractConstant = new WorseExtractConstant($this->reflectorForWorkspace($source), $this->updater());
        $textDocumentEdits = $extractConstant->extractConstant(SourceCode::fromStringAndPath($source, 'file:///source'), $offset, $name);
        $sourceCode = SourceCode::fromStringAndPath($source, 'file:///source');
        $transformed = SourceCode::fromStringAndPath(
            (string) $textDocumentEdits->textEdits()->apply($sourceCode),
            $textDocumentEdits->uri()->path()
        );

        $this->assertEquals(trim($expected), trim($transformed));
    }

    public function provideExtractMethod()
    {
        return [
            'string' => [
                'extractConstant1.test',
                'HELLO_WORLD'
            ],
            'numeric' => [
                'extractConstant2.test',
                'HELLO_WORLD'
            ],
            'array_key' => [
                'extractConstant3.test',
                'HELLO_WORLD'
            ],
            'namespaced' => [
                'extractConstant4.test',
                'HELLO_WORLD'
            ],
            'replace all' => [
                'extractConstant5.test',
                'HELLO_WORLD'
            ],
            'replace all numeric' => [
                'extractConstant6.test',
                'HOUR'
            ],
        ];
    }

    public function testNoClass(): void
    {
        $this->expectException(TransformException::class);
        $this->expectExceptionMessage('Node does not belong to a class');

        $code = <<<'EOT'
            <?php 1234;
            EOT
        ;

        $extractConstant = new WorseExtractConstant($this->reflectorForWorkspace($code), $this->updater());
        $transformed = $extractConstant->extractConstant(SourceCode::fromString($code), 8, 'asd');
    }
}
