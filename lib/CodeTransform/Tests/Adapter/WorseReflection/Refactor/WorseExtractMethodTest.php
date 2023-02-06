<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseExtractMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\WorseReflection\Core\SourceCode as WorseSourceCode;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;
use Exception;

class WorseExtractMethodTest extends WorseTestCase
{
    /**
     * @dataProvider provideExtractMethod
     */
    public function testExtractMethod(string $test, string $name, ?string $expectedExceptionMessage = null): void
    {
        [$source, $expected, $offsetStart, $offsetEnd] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);

        if ($expectedExceptionMessage) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $worseSourceCode = WorseSourceCode::fromPathAndString('file:///source', $source);
        $reflector = $this->reflectorForWorkspace($worseSourceCode);

        $factory = new WorseBuilderFactory($reflector);
        $extractMethod = new WorseExtractMethod($reflector, $factory, $this->updater());

        $sourceCode = SourceCode::fromStringAndPath($source, 'file:///source');
        $textDocumentEdits = $extractMethod->extractMethod($sourceCode, $offsetStart, $offsetEnd, $name);

        $transformed = SourceCode::fromStringAndPath(
            (string) $textDocumentEdits->textEdits()->apply($sourceCode),
            $textDocumentEdits->uri()->path()
        );
        $this->assertEquals(trim($expected), trim($transformed));
    }

    public function provideExtractMethod(): array
    {
        return [
            'no free variables' => [
                'extractMethod1.test',
                'newMethod'
            ],
            'free variable' => [
                'extractMethod2.test',
                'newMethod'
            ],
            'free variables' => [
                'extractMethod3.test',
                'newMethod'
            ],
            'namespaced' => [
                'extractMethod4.test',
                'newMethod'
            ],
            'duplicated vars' => [
                'extractMethod5.test',
                'newMethod'
            ],
            'return value and assignment' => [
                'extractMethod6.test',
                'newMethod'
            ],
            'multiple return value and assignment' => [
                'extractMethod7.test',
                'newMethod'
            ],
            'multiple return value with incoming variables' => [
                'extractMethod8.test',
                'newMethod'
            ],
            'multiple return value boundaries' => [
                'extractMethod10.test',
                'newMethod',
            ],
            'method exists' => [
                'extractMethod9.test',
                'newMethod',
                'Class "extractMethod" already has method "newMethod"'
            ],
            'tail variables are taken from scope' => [
                'extractMethod11.test',
                'newMethod',
            ],
            'replacement indentation is preserved' => [
                'extractMethod12.test',
                'newMethod',
            ],
            'only considers selection content for return vars' => [
                'extractMethod13.test',
                'newMethod',
            ],
            'return mutated primative' => [
                'extractMethod14.test',
                'newMethod',
            ],
            'imports classes' => [
                'extractMethod15.test',
                'newMethod',
            ],
            'adds return type for scalar' => [
                'extractMethod16.test',
                'newMethod',
            ],
            'adds return type for nullable scalar' => [
                'extractMethod16A.test',
                'newMethod',
            ],
            'adds return type and import for nullable class' => [
                'extractMethod16B.test',
                'newMethod',
            ],
            'adds return type for class' => [
                'extractMethod17.test',
                'newMethod',
            ],
            'extracts expression to method' => [
                'extractMethod18.test',
                'newMethod',
            ],
            'extracts assignment expression to method' => [
                'extractMethod19.test',
                'newMethod',
            ],
            'extracts assignment expression with unknown return type' => [
                'extractMethod20.test',
                'newMethod',
            ],
            'extract expression and adds short return type for class' => [
                'extractMethod21.test',
                'newMethod',
            ],
            'return if extracted code has a return' => [
                'extractMethod22.test',
                'newMethod',
            ],
            'adds method to declaring class' => [
                'extractMethod23.test',
                'newMethod',
            ],
            'different scopes 1' => [
                'extractMethod24.test',
                'newMethod',
                'Cannot extract method. Check if start and end statements are in different scopes.'
            ],
            'different scopes 2' => [
                'extractMethod25.test',
                'newMethod',
                'Cannot extract method. Check if start and end statements are in different scopes.'
            ],
            'different scopes 3' => [
                'extractMethod26.test',
                'newMethod',
                'Cannot extract method. Check if start and end statements are in different scopes.'
            ],
            'empty text selection' => [
                'extractMethod27.test',
                'newMethod',
            ],
            'empty selection 2' => [
                'extractMethod28.test',
                'newMethod',
                'Cannot extract method. Check if start and end statements are in different scopes.'
            ],
            'nullable argument' => [
                'extractMethod29.test',
                'newMethod',
            ],
            'ignore scoped variables: catch clause' => [
                'extractMethod30.test',
                'newMethod',
            ],
            'ignore scoped variables: anonymous function' => [
                'extractMethod31.test',
                'newMethod',
            ],
            'union argument' => [
                'extractMethod32.test',
                'newMethod',
            ],
            'extract method from trait' => [
                'extractMethod33.test',
                'newMethod'
            ],
        ];
    }
}
