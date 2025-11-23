<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Builder;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatement;
use Closure;

class SourceCodeBuilderTest extends TestCase
{
    #[DataProvider('provideModificationTracking')]
    public function testModificationTracking(Closure $setup, Closure $assertion): void
    {
        $builder = $this->builder();
        $setup($builder);
        $assertion($builder);

        $builder->class('Hello')->method('goodbye');
        $this->assertTrue($builder->isModified(), 'method has been modified since last snapshot');
    }

    public function provideModificationTracking(): Generator
    {
        yield 'new builder is modified by default' => [
            function (SourceCodeBuilder $builder): void {
                $builder->class('foobar');
            },
            function (SourceCodeBuilder $builder): void {
                $this->assertTrue($builder->isModified());
            }
        ];

        yield 'is not modified after snapshot' => [
            function (SourceCodeBuilder $builder): void {
                $builder->class('foobar');
                $builder->snapshot();
            },
            function (SourceCodeBuilder $builder): void {
                $this->assertFalse($builder->isModified());
            }
        ];

        yield 'is not modified if updated values are the same 1' => [
            function (SourceCodeBuilder $builder): void {
                $builder->class('foobar')->method('foobar')->parameter('barfoo');
                $builder->snapshot();
                $builder->class('foobar')->method('foobar')->parameter('barfoo');
            },
            function (SourceCodeBuilder $builder): void {
                $this->assertFalse($builder->isModified());
            }
        ];

        yield 'is not modified if updated values are the same 2' => [
            function (SourceCodeBuilder $builder): void {
                $builder->class('foobar')->method('foobar');
                $builder->snapshot();
                $builder->class('foobar')->method('foobar');
            },
            function (SourceCodeBuilder $builder): void {
                $this->assertFalse($builder->isModified());
            }
        ];

        yield 'is modified when values are different 1' => [
            function (SourceCodeBuilder $builder): void {
                $builder->class('foobar')->method('foobar');
                $builder->snapshot();
                $builder->class('foobar')->method('barbarr');
            },
            function (SourceCodeBuilder $builder): void {
                $this->assertTrue($builder->isModified());
            }
        ];

        yield 'is modified when values are different 2' => [
            function (SourceCodeBuilder $builder): void {
                $builder->class('foobar')->method('foobar')->parameter('barbar');
                $builder->snapshot();
                $builder->class('foobar')->method('barbar')->parameter('fofo');
            },
            function (SourceCodeBuilder $builder): void {
                $this->assertTrue($builder->isModified());
            }
        ];
    }

    public function testSourceCodeBuilderUse(): void
    {
        $builder = $this->builder();
        $builder->namespace('Barfoo');
        $builder->use('Foobar');
        $builder->use('Foobar');
        $builder->use('Barfoo');
        $builder->class('Hello');
        $builder->trait('Goodbye');

        $code = $builder->build();

        $this->assertInstanceOf(SourceCode::class, $code);
        $this->assertEquals('Barfoo', $code->namespace()->__toString());
        $this->assertCount(2, $code->useStatements());
        $this->assertEquals('Barfoo', $code->useStatements()->sorted()->first()->__toString());
        $this->assertEquals('Foobar', $code->useStatements()->first()->__toString());
        $this->assertEquals('Hello', $code->classes()->first()->name());
        $this->assertEquals('Goodbye', $code->traits()->first()->name());
    }

    public function testFunctionUse(): void
    {
        $builder = $this->builder();
        $builder->useFunction('hello');
        $builder->useFunction('hello\goodbye');
        $code = $builder->build();

        $this->assertCount(2, $code->useStatements());
        $this->assertEquals('hello', $code->useStatements()->first()->__toString());
        $this->assertEquals(UseStatement::TYPE_FUNCTION, $code->useStatements()->first()->type());
    }

    public function testClassBuilder(): void
    {
        $builder = $this->builder();
        $classBuilder = $builder->class('Dog')
            ->extends('Canine')
            ->implements('Teeth')
            ->property('one')->end()
            ->property('two')->end()
            ->method('method1')->end()
            ->method('method2')->end();

        $class = $classBuilder->build();

        $this->assertSame($classBuilder, $builder->class('Dog'));
        $this->assertEquals('Canine', $class->extendsClass()->__toString());
        $this->assertEquals('Teeth', $class->implementsInterfaces()->first());
        $this->assertEquals('one', $class->properties()->first()->name());
        $this->assertEquals('method1', $class->methods()->first()->name());
    }

    public function testClassBuilderAddMethodBuilder(): void
    {
        $builder = $this->builder();
        $methodBuilder = $this->builder()->class('Cat')->method('Whiskers');
        $classBuilder = $builder->class('Dog');
        $classBuilder->add($methodBuilder);

        $this->assertSame($classBuilder->method('Whiskers'), $methodBuilder);
    }

    public function testClassBuilderAddPropertyBuilder(): void
    {
        $builder = $this->builder();
        $propertyBuilder = $this->builder()->class('Cat')->property('whiskers');
        $classBuilder = $builder->class('Dog');
        $classBuilder->add($propertyBuilder);

        $this->assertSame($classBuilder->property('whiskers'), $propertyBuilder);
    }

    public function testInterfaceBuilder(): void
    {
        $builder = $this->builder();
        $interfaceBuilder = $builder->interface('Dog')
            ->extends('Canine')
            ->method('method1')->end()
            ->method('method2')->end();

        $class = $interfaceBuilder->build();

        $this->assertSame($interfaceBuilder, $builder->interface('Dog'));
    }

    public function testTraitBuilder(): void
    {
        $builder = $this->builder();
        $traitBuilder = $builder->trait('Dog')
            ->property('one')->end()
            ->property('two')->end()
            ->method('method1')->end()
            ->method('method2')->end();

        $trait = $traitBuilder->build();

        $this->assertSame($traitBuilder, $builder->trait('Dog'));
        $this->assertEquals('one', $trait->properties()->first()->name());
        $this->assertEquals('method1', $trait->methods()->first()->name());
    }

    public function testTraitBuilderAddMethodBuilder(): void
    {
        $builder = $this->builder();
        $methodBuilder = $this->builder()->trait('Cat')->method('Whiskers');
        $traitBuilder = $builder->trait('Dog');
        $traitBuilder->add($methodBuilder);

        $this->assertSame($traitBuilder->method('Whiskers'), $methodBuilder);
    }

    public function testTraitBuilderAddPropertyBuilder(): void
    {
        $builder = $this->builder();
        $propertyBuilder = $this->builder()->trait('Cat')->property('whiskers');
        $traitBuilder = $builder->trait('Dog');
        $traitBuilder->add($propertyBuilder);

        $this->assertSame($traitBuilder->property('whiskers'), $propertyBuilder);
    }

    public function testPropertyBuilder(): void
    {
        $builder = $this->builder();
        $propertyBuilder = $builder->class('Dog')->property('one')
            ->type('string')
            ->defaultValue(null);

        $property = $propertyBuilder->build();

        $this->assertEquals('string', $property->type()->__toString());
        $this->assertEquals('null', $property->defaultValue()->export());
        $this->assertSame($propertyBuilder, $builder->class('Dog')->property('one'));
    }

    public function testClassMethodBuilderAccess(): void
    {
        $builder = $this->builder();
        $methodBuilder = $builder->class('Bar')->method('foo');

        $this->assertSame($methodBuilder, $builder->class('Bar')->method('foo'));
    }

    public function testTraitMethodBuilderAccess(): void
    {
        $builder = $this->builder();
        $methodBuilder = $builder->trait('Bar')->method('foo');

        $this->assertSame($methodBuilder, $builder->trait('Bar')->method('foo'));
    }

    #[DataProvider('provideMethodBuilder')]
    public function testMethodBuilder(MethodBuilder $methodBuilder, Closure $assertion): void
    {
        $builder = $this->builder();
        $method = $methodBuilder->build();
        $assertion($method);
    }

    /** @return Generator<string, array{0: MethodBuilder, 1: Closure}> */
    public function provideMethodBuilder(): Generator
    {
        yield 'Method return type' => [
            $this->builder()->class('Dog')->method('one')
                ->returnType('?string')
                ->visibility('private')
                ->parameter('one')
                ->type('One')
                ->defaultValue(1)
                ->end(),
            function (Method $method): void {
                $this->assertEquals('?string', $method->returnType()->__toString());
            }
        ];

        yield 'One method modifier' => [
            $this->builder()->class('Dog')->method('one')->static()->abstract(),
            function ($method): void {
                $this->assertTrue($method->isStatic());
                $this->assertTrue($method->isAbstract());
            }
        ];
        yield 'Two method modifiers' => [
            $this->builder()->class('Dog')->method('one')->abstract(),
            function ($method): void {
                $this->assertFalse($method->isStatic());
                $this->assertTrue($method->isAbstract());
            }
        ];
        yield 'Method lines' => [
            $this->builder()->class('Dog')->method('one')->body()->line('one')->line('two')->end(),
            function ($method): void {
                $this->assertCount(2, $method->body()->lines());
                $this->assertEquals('one', (string) $method->body()->lines()->first());
            }
        ];
    }

    public function testParameterBuilder(): void
    {
        $builder = $this->builder();
        $method = $builder->class('Bar')->method('foo');
        $parameterBuilder = $method->parameter('foo');

        $this->assertSame($parameterBuilder, $method->parameter('foo'));
    }

    private function builder(): SourceCodeBuilder
    {
        return SourceCodeBuilder::create();
    }
}
