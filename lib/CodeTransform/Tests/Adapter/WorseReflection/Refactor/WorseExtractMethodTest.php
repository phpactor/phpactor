<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Refactor;

use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseExtractMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;
use Exception;
use Generator;
use Phpactor\TextDocument\TextDocumentBuilder;

class WorseExtractMethodTest extends WorseTestCase
{
    /**
     * @dataProvider provideExtractMethod
     */
    public function testExtractMethod(string $test): void
    {
        [$source, $expected, $offsetStart, $offsetEnd] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);

        $worseSourceCode = TextDocumentBuilder::fromPathAndString('file:///source', $source);
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

    /** @return Generator<array<string>> */
    public function provideExtractMethod(): Generator
    {
        yield 'no free variables' => ['extractMethod1.test'];
        yield 'free variable' => ['extractMethod2.test'];
        yield 'free variables' => ['extractMethod3.test'];
        yield 'namespaced' => ['extractMethod4.test'];
        yield 'duplicated vars' => ['extractMethod5.test'];
        yield 'return value and assignment' => ['extractMethod6.test'];
        yield 'multiple return value and assignment' => ['extractMethod7.test'];
        yield 'multiple return value with incoming variables' => ['extractMethod8.test'];
        yield 'multiple return value boundaries' => ['extractMethod10.test'];
        yield 'tail variables are taken from scope' => ['extractMethod11.test'];
        yield 'replacement indentation is preserved' => ['extractMethod12.test'];
        yield 'only considers selection content for return vars' => ['extractMethod13.test'];
        yield 'return mutated primative' => ['extractMethod14.test'];
        yield 'imports classes' => ['extractMethod15.test'];
        yield 'adds return type for scalar' => ['extractMethod16.test'];
        yield 'adds return type for nullable scalar' => ['extractMethod16A.test'];
        yield 'adds return type and import for nullable class' => ['extractMethod16B.test'];
        yield 'adds return type for class' => ['extractMethod17.test'];
        yield 'extracts expression to method' => ['extractMethod18.test'];
        yield 'extracts assignment expression to method' => ['extractMethod19.test'];
        yield 'extracts assignment expression with unknown return type' => ['extractMethod20.test'];
        yield 'extract expression and adds short return type for class' => ['extractMethod21.test'];
        yield 'return if extracted code has a return' => ['extractMethod22.test'];
        yield 'adds method to declaring class' => ['extractMethod23.test'];
        yield 'empty text selection' => ['extractMethod27.test'];
        yield 'nullable argument' => ['extractMethod29.test'];
        yield 'ignore scoped variables: catch clause' => ['extractMethod30.test'];
        yield 'ignore scoped variables: anonymous function' => ['extractMethod31.test'];
        yield 'union argument' => ['extractMethod32.test'];
        yield 'extract method from trait' => ['extractMethod33.test'];
        yield 'extract static method' => ['extractMethod34.test'];
        yield 'inside if statement with a minimal whitespace' => ['extractMethod35.test'];
    }

    /**
     * @dataProvider provideExtractMethodFromDifferentScopes
     */
    public function testExtractingMethodsFromDifferentScopes(string $test): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot extract method. Check if start and end statements are in different scopes.');

        $this->testExtractMethod($test);
    }

    /** @return Generator<array<string>> */
    public function provideExtractMethodFromDifferentScopes(): Generator
    {
        yield['extractMethod24.test'];
        yield['extractMethod25.test'];
        yield['extractMethod26.test'];
        yield 'empty selection 2' => [
            'extractMethod28.test',
        ];
    }

    public function testExtractMethodThatExists(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Class "extractMethod" already has method "newMethod"');

        $this->testExtractMethod('extractMethod_methodExists.test');
    }
}
