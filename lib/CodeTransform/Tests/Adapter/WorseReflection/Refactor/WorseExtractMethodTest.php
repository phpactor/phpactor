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
    public function testExtractMethod(string $test,
        ?string $expectedExceptionMessage = null
    ): void {
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
        $textDocumentEdits = $extractMethod->extractMethod($sourceCode, $offsetStart, $offsetEnd, 'newMethod');

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
            ],
            'free variable' => [
                'extractMethod2.test',
            ],
            'free variables' => [
                'extractMethod3.test',
            ],
            'namespaced' => [
                'extractMethod4.test',
            ],
            'duplicated vars' => [
                'extractMethod5.test',
            ],
            'return value and assignment' => [
                'extractMethod6.test',
            ],
            'multiple return value and assignment' => [
                'extractMethod7.test',
            ],
            'multiple return value with incoming variables' => [
                'extractMethod8.test',
            ],
            'multiple return value boundaries' => [
                'extractMethod10.test',
            ],
            'method exists' => [
                'extractMethod9.test',
                'newMethod',
                'Class "extractMethod" already has method "newMethod"'
            ],
            'tail variables are taken from scope' => [
                'extractMethod11.test',
            ],
            'replacement indentation is preserved' => [
                'extractMethod12.test',
            ],
            'only considers selection content for return vars' => [
                'extractMethod13.test',
            ],
            'return mutated primative' => [
                'extractMethod14.test',
            ],
            'imports classes' => [
                'extractMethod15.test',
            ],
            'adds return type for scalar' => [
                'extractMethod16.test',
            ],
            'adds return type for nullable scalar' => [
                'extractMethod16A.test',
            ],
            'adds return type and import for nullable class' => [
                'extractMethod16B.test',
            ],
            'adds return type for class' => [
                'extractMethod17.test',
            ],
            'extracts expression to method' => [
                'extractMethod18.test',
            ],
            'extracts assignment expression to method' => [
                'extractMethod19.test',
            ],
            'extracts assignment expression with unknown return type' => [
                'extractMethod20.test',
            ],
            'extract expression and adds short return type for class' => [
                'extractMethod21.test',
            ],
            'return if extracted code has a return' => [
                'extractMethod22.test',
            ],
            'adds method to declaring class' => [
                'extractMethod23.test',
            ],
            'different scopes 1' => [
                'extractMethod24.test',
                'Cannot extract method. Check if start and end statements are in different scopes.'
            ],
            'different scopes 2' => [
                'extractMethod25.test',
                'Cannot extract method. Check if start and end statements are in different scopes.'
            ],
            'different scopes 3' => [
                'extractMethod26.test',
                'Cannot extract method. Check if start and end statements are in different scopes.'
            ],
            'empty text selection' => [
                'extractMethod27.test',
            ],
            'empty selection 2' => [
                'extractMethod28.test',
                'Cannot extract method. Check if start and end statements are in different scopes.'
            ],
            'nullable argument' => [
                'extractMethod29.test',
            ],
            'ignore scoped variables: catch clause' => [
                'extractMethod30.test',
            ],
            'ignore scoped variables: anonymous function' => [
                'extractMethod31.test',
            ],
            'union argument' => [
                'extractMethod32.test',
            ],
            'extract method from trait' => [
                'extractMethod33.test',
            ],
            'extract static method' => [
                'extractMethod34.test'
            ]
        ];
    }
}
