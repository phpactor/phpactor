<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Closure;

class ReflectionParameterTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionParameter
     */
    public function testReflectParameter(string $source, Closure $assertion): void
    {
        $source = sprintf('<?php namespace Acme; class Foobar { public function method(%s) }', $source);
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString('Acme\Foobar'));
        $assertion($class->methods()->get('method'));
    }

    public function provideReflectionParameter()
    {
        yield 'It reflects a an empty list with no parameters' => [
            '',
            function ($method): void {
                $this->assertCount(0, $method->parameters());
            },
        ];

        yield 'It reflects a single parameter' => [
            '$foobar',
            function ($method): void {
                $this->assertCount(1, $method->parameters());
                $parameter = $method->parameters()->get('foobar');
                $this->assertInstanceOf(ReflectionParameter::class, $parameter);
                $this->assertEquals('foobar', $parameter->name());
            },
        ];

        yield 'It returns false if the parameter has no type' => [
            '$foobar',
            function ($method): void {
                $this->assertTrue(
                    $method->parameters()->get('foobar')->type() instanceof MissingType
                );
            },
        ];

        yield 'It returns the parameter type' => [
            'Foobar $foobar',
            function ($method): void {
                $this->assertEquals('Acme\Foobar', $method->parameters()->get('foobar')->type()->__toString());
            },
        ];

        yield 'It returns false if the parameter has no default' => [
            '$foobar',
            function ($method): void {
                $this->assertFalse($method->parameters()->get('foobar')->default()->isDefined());
            },
        ];

        yield 'It returns the default value for a string' => [
            '$foobar = "foo"',
            function ($method): void {
                $this->assertTrue($method->parameters()->get('foobar')->default()->isDefined());
                $this->assertEquals(
                    'foo',
                    $method->parameters()->get('foobar')->default()->value()
                );
            },
        ];

        yield 'It returns the default value for a number' => [
            '$foobar = 1234',
            function ($method): void {
                $this->assertEquals(
                    1234,
                    $method->parameters()->get('foobar')->default()->value()
                );
            },
        ];

        yield 'It returns the default value for an array' => [
            '$foobar = [ "foobar" ]',
            function ($method): void {
                $this->assertEquals(
                    ['foobar'],
                    $method->parameters()->get('foobar')->default()->value()
                );
            },
        ];

        yield 'It returns the default value for null' => [
            '$foobar = null',
            function ($method): void {
                $this->assertEquals(
                    null,
                    $method->parameters()->get('foobar')->default()->value()
                );
            },
        ];

        yield 'It returns the default value for empty array' => [
            '$foobar = []',
            function ($method): void {
                $foobar = $method->parameters()->get('foobar');
                $this->assertEquals(
                    [],
                    $foobar->default()->value()
                );
            },
        ];

        yield 'It returns the default value for a boolean' => [
            '$foobar = false',
            function ($method): void {
                $this->assertEquals(
                    false,
                    $method->parameters()->get('foobar')->default()->value()
                );
            },
        ];

        yield 'Passed by reference' => [
            '&$foobar',
            function ($method): void {
                $this->assertTrue(
                    $method->parameters()->get('foobar')->byReference()
                );
            },
        ];

        yield 'Not passed by reference' => [
            '$foobar',
            function ($method): void {
                $this->assertFalse(
                    $method->parameters()->get('foobar')->byReference()
                );
            },
        ];

        yield 'It reflects iterable type properly' => [
            'iterable $foobar',
            function ($method): void {
                $this->assertEquals(
                    TypeFactory::fromString('iterable'),
                    $method->parameters()->get('foobar')->type()
                );
            },
        ];

        yield 'It reflects resource type properly' => [
            'resource $foobar',
            function ($method): void {
                $this->assertEquals(
                    TypeFactory::fromString('resource'),
                    $method->parameters()->get('foobar')->type()
                );
            },
        ];

        yield 'It reflects callable type properly' => [
            'callable $foobar',
            function ($method): void {
                $this->assertEquals(
                    TypeFactory::fromString('callable'),
                    $method->parameters()->get('foobar')->type()
                );
            },
        ];

        yield 'It reflects a nullable parameter' => [
            '?string $foobar',
            function ($method): void {
                $this->assertEquals(
                    TypeFactory::nullable(TypeFactory::string()),
                    $method->parameters()->get('foobar')->type()
                );
            },
        ];

        yield 'It reflects a promoted parameter' => [
            'private string $foobar',
            function ($method): void {
                $this->assertTrue(
                    $method->parameters()->get('foobar')->isPromoted()
                );
            },
        ];

        yield 'It reflects a (not) promoted parameter' => [
            'string $foobar',
            function ($method): void {
                $this->assertFalse(
                    $method->parameters()->get('foobar')->isPromoted()
                );
            },
        ];
    }

    /**
     * @dataProvider provideReflectionParameterWithDocblock
     */
    public function testReflectParameterWithDocblock(string $source, string $docblock, Closure $assertion): void
    {
        $source = sprintf('<?php namespace Acme; class Foobar { %s public function method(%s) }', $docblock, $source);
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString('Acme\Foobar'));
        $assertion($class->methods()->get('method'));
    }

    public function provideReflectionParameterWithDocblock()
    {
        yield 'It returns docblock parameter type' => [
            '$foobar',
            '/** @param Foobar $foobar */',
            function ($method): void {
                $this->assertCount(1, $method->parameters());
                $this->assertEquals('Acme\Foobar', (string) $method->parameters()->get('foobar')->inferredTypes()->best());
            },
        ];

        yield 'It returns unknown type when no type hinting is available' => [
            '$foobar',
            '/** */',
            function ($method): void {
                $this->assertCount(1, $method->parameters());
                $this->assertEquals(TypeFactory::unknown(), $method->parameters()->get('foobar')->inferredTypes()->best());
            },
        ];
    }
}
