<?php

namespace Phpactor\Completion\Tests\Unit\Bridge\WorseReflection\Formatter;

use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\FunctionLikeSnippetFormatter;
use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\ParametersSnippetFormatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Completion\Tests\TestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\ReflectorBuilder;

final class FunctionLikeSnippetFormatterTest extends TestCase
{
    /**
     * @dataProvider provideReflectionToFormat
     */
    public function testFormat(ReflectionFunctionLike $reflection, string $expected): void
    {
        $this->assertEquals(
            $expected,
            $this->format($reflection)
        );
    }

    public function provideReflectionToFormat(): iterable
    {
        yield 'Function without parameters' => [
            $this->reflectFunction('func()'),
            'func()',
        ];

        yield 'Function with mandatory parameters' => [
            $this->reflectFunction('func(string $test, int $i)'),
            'func(${1:\$test}, ${2:\$i})${0}'
        ];

        yield 'Function with mandatory and optional parameters' => [
            $this->reflectFunction('func(string $test, int $i = 1)'),
            'func(${1:\$test})${0}'
        ];

        yield 'Function with only optional parameters' => [
            $this->reflectFunction('func(?string $test = null, int $i = 1)'),
            'func(${1})${0}'
        ];

        yield 'Method without parameters' => [
            $this->reflectMethod('method()'),
            'method()'
        ];

        yield 'Method with mandatory parameters' => [
            $this->reflectMethod('method(string $test, int $i)'),
            'method(${1:\$test}, ${2:\$i})${0}'
        ];

        yield 'Method with mandatory and optional parameters' => [
            $this->reflectMethod('method(string $test, int $i = 1)'),
            'method(${1:\$test})${0}'
        ];

        yield 'Method with only optional parameters' => [
            $this->reflectMethod('method(?string $test = null, int $i = 1)'),
            'method(${1})${0}'
        ];
    }

    private function format(ReflectionFunctionLike $reflection): string
    {
        return (new FunctionLikeSnippetFormatter())
            ->format(new ObjectFormatter([
                new ParametersSnippetFormatter()
            ]), $reflection);
    }

    private function reflectFunction(string $functionAsString): ReflectionFunction
    {
        return ReflectorBuilder::create()
            ->build()
            ->reflectFunctionsIn(TextDocumentBuilder::fromUnknown(\sprintf('<?php function %s {}', $functionAsString)))
            ->first()
        ;
    }

    private function reflectMethod(string $methodAsString): ReflectionMethod
    {
        return ReflectorBuilder::create()
            ->build()
            ->reflectClassLikesIn(TextDocumentBuilder::fromUnknown(\sprintf('<?php class Foo { public function %s {} }', $methodAsString)))
            ->first()
            ->methods()
            ->first()
        ;
    }
}
