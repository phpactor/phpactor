<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateDecorator;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Domain\SourceCode;

class WorseGenerateDecoratorTest extends WorseTestCase
{
    /**
     * @dataProvider provideGenerateDecorator
     */
    public function testGenerateDecorator(string $test): void
    {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);
        $sourceCode = SourceCode::fromStringAndPath($source, 'file:///source');

        $generateDecorator = new WorseGenerateDecorator($this->reflectorForWorkspace($source), $this->updater());
        $textDocumentEdits = $generateDecorator->getTextEdits($sourceCode, 'Phpactor\\SomethingToDecorate');

        $transformed = SourceCode::fromStringAndPath(
            (string) $textDocumentEdits->apply($sourceCode),
            'file:///source'
        );

        $this->assertEquals(trim($expected), trim($transformed));
    }

    /**
     * @return array<string,array{string}>
     */
    public function provideGenerateDecorator(): array
    {
        return [
            'decorating untyped method' => [ 'generateDecorator1.test'],
            'decorating method with parameters' => [ 'generateDecorator2.test'],
            'decorating method with return type' => [ 'generateDecorator3.test'],
            'decorating method with default values' => [ 'generateDecorator4.test'],
            'decorating method with void' => [ 'generateDecorator5.test'],
            'decorating multiple methods' => [ 'generateDecorator6.test']
        ];
    }
}
