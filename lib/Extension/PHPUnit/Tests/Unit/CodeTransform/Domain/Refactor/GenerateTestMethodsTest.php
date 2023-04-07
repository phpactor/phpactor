<?php

namespace Phpactor\Extension\PHPUnit\Tests\Unit\CodeTransform\Domain\Refactor;

use Generator;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\Extension\PHPUnit\CodeTransform\Domain\Refactor\GenerateTestMethods;

class GenerateTestMethodsTest extends WorseTestCase
{
    /**
     * @param array<string> $expected
     *
     * @dataProvider dataCanMethodBeGenerated
     */
    public function testCanMethodBeGenerated(string $source, array $expected): void {
        $sourceCode = SourceCode::fromStringAndPath('<?php '.$source, 'file:///source');

        $generateDecorator = new GenerateTestMethods($this->reflectorForWorkspace($source), $this->updater());
        $methodNames = $generateDecorator->getGeneratableTestMethods($sourceCode);

        $this->assertEquals($expected, iterator_to_array($methodNames));
    }

    /**
     * @return Generator<string, array{string, array<string>}>
     */
    public function dataCanMethodBeGenerated(): Generator
    {
        yield 'no classes' => ['echo "Hello"', []];

        yield 'not a phpunit class' => [
            <<<PHP
            class SomeClass {
            }
            PHP,
            [],
        ];

        yield 'a phpunit class' => [
            <<<PHP
            class SomeClass extends PHPUnit\Framework\TestCase {
            }
            PHP,
            ['setUp'],
        ];
    }

    //public function testGenerateTestMethod(string $test): void {
    //}

}
