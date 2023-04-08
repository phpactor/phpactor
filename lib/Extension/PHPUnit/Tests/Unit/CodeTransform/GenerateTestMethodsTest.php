<?php

namespace Phpactor\Extension\PHPUnit\Tests\Unit\CodeTransform;

use Generator;
use InvalidArgumentException;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\Extension\PHPUnit\CodeTransform\GenerateTestMethods;

class GenerateTestMethodsTest extends WorseTestCase
{
    public function setUp(): void
    {
        // Adding a phpunit stub
        $this->workspace()->put('TestCase.php', <<<PHP
                <?php

                namespace PHPUnit\Framework;

                class TestCase {}
            PHP);
    }

    /**
     * @param array<string> $expected
     *
     * @dataProvider dataCanMethodBeGenerated
     */
    public function testCanMethodBeGenerated(string $source, array $expected): void
    {
        $sourceCode = SourceCode::fromStringAndPath('<?php '.$source, 'file:///source');

        $methodNames = $this->createTestMethodGenerator($source)->getGeneratableTestMethods($sourceCode);

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
                use PHPUnit\Framework\TestCase;

                class SomeTest extends TestCase {
                }
                PHP,
            ['setUp', 'tearDown'],
        ];

        yield 'do not generate already existing methods' => [
            <<<PHP
                use PHPUnit\Framework\TestCase;

                class SomeTest extends TestCase {
                    public function tearDown() {}
                }
                PHP,
            ['setUp'],
        ];
    }

    /**
     * @dataProvider provideGenerateTestMethods
     */
    public function testGenerateDecorator(string $test): void
    {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);
        $sourceCode = SourceCode::fromStringAndPath($source, 'file:///source');

        $textDocumentEdits = $this->createTestMethodGenerator($source)->generateMethod($sourceCode, 'setUp');

        $transformed = SourceCode::fromStringAndPath(
            (string) $textDocumentEdits->apply($sourceCode),
            'file:///source'
        );

        $this->assertEquals(trim($expected), trim($transformed));
    }

    /**
     * @return array<string,array{string}>
     */
    public function provideGenerateTestMethods(): array
    {
        return [
            //'generating a method that already exists' => [ 'generateTestMethods1.test'],
            //'generating a new setUp method' => [ 'generateTestMethods2.test'],
        ];
    }

    public function testGeneratingOtherMethods(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Phpactor\Extension\PHPUnit\CodeTransform\GenerateTestMethods can not generate "someRandomMethod" with class'
        );

        $sourceCode = SourceCode::fromStringAndPath('', 'file:///source');
        $this->createTestMethodGenerator('')->generateMethod($sourceCode, 'someRandomMethod');
    }

    private function createTestMethodGenerator(string $source): GenerateTestMethods
    {
        return new GenerateTestMethods($this->reflectorForWorkspace($source), $this->updater());
    }
}
