<?php

namespace Phpactor\CodeBuilder\Tests\Adapter\WorseReflection;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Method;
use Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider\DocblockMemberProvider;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseBuilderFactoryTest extends TestCase
{
    public function testEmptySource(): void
    {
        $source = $this->build('<?php ');
        $this->assertInstanceOf(SourceCode::class, $source);
    }

    public function testSimpleClass(): void
    {
        $source = $this->build('<?php class Foobar {}');
        $this->assertEquals('Foobar', $this->getFirstClass($source)->name());
    }

    public function testSimpleClassWithNamespace(): void
    {
        $source = $this->build('<?php namespace Foobar; class Foobar {}');
        $this->getFirstClass($source);
        $this->assertEquals('Foobar', $source->namespace());
    }

    public function testClassWithProperty(): void
    {
        $source = $this->build('<?php class Foobar { public $foo; }');
        $firstClass = $this->getFirstClass($source);

        $this->assertCount(1, $firstClass->properties());
        $this->assertEquals('foo', $firstClass->properties()->first()?->name());
    }

    public function testClassWithProtectedProperty(): void
    {
        $source = $this->build('<?php class Foobar { private $foo; }');
        $firstClass = $this->getFirstClass($source);
        $this->assertCount(1, $firstClass->properties());
        $this->assertEquals('private', (string) $firstClass->properties()->first()?->visibility());
    }

    public function testClassWithPropertyDefaultValue(): void
    {
        $this->markTestSkipped('Worse reflection doesn\'t support default property values atm');
        $source = $this->build('<?php class Foobar { private $foo = "foobar"; }');
        $this->assertEquals('foobar', $this->getFirstClass($source)->properties()->first()?->defaultValue()->export());
    }

    public function testClassWithPropertyTyped(): void
    {
        $source = $this->build('<?php class Foobar { /** @var Foobar */private $foo = "foobar"; }');
        $this->assertEquals('Foobar', $this->getFirstClass($source)->properties()->first()?->type()->__toString());
    }

    public function testClassWithPropertyScalarTyped(): void
    {
        $source = $this->build('<?php class Foobar { /** @var string */private $foo = "foobar"; }');
        $this->assertEquals('string', $this->getFirstClass($source)->properties()->first()?->type()->__toString());
    }

    public function testClassWithPropertyImportedType(): void
    {
        $source = $this->build('<?php use Bar\Foobar; class Foobar { /** @var Foobar */private $foo = "foobar"; }');
        $this->assertEquals('Foobar', $this->getFirstClass($source)->properties()->first()?->type()->__toString());
        $this->assertEquals('Bar\Foobar', (string) $source->useStatements()->first());
    }

    public function testSimpleTrait(): void
    {
        $source = $this->build('<?php trait Foobar {}');
        $traits = $source->traits();
        $this->assertCount(1, $traits);
        $this->assertEquals('Foobar', $traits->first()?->name());
    }

    public function testSimpleTraitWithNamespace(): void
    {
        $source = $this->build('<?php namespace Foobar; trait Foobar {}');
        $traits = $source->traits();
        $this->assertCount(1, $traits);
        $this->assertEquals('Foobar', $source->namespace());
    }

    public function testTraitWithProperty(): void
    {
        $source = $this->build('<?php trait Foobar { public $foo; }');
        $firstTrait = $source->traits()->first();

        $this->assertNotNull($firstTrait);
        $this->assertCount(1, $firstTrait->properties());
        $this->assertEquals('foo', $firstTrait->properties()->first()?->name());
    }

    public function testTraitWithMethod(): void
    {
        $source = $this->build('<?php trait Foobar { public function method() {} }');
        $this->assertEquals('method', $source->traits()->first()?->methods()->first()?->name());
    }

    public function testMethod(): void
    {
        $source = $this->build('<?php class Foobar { public function method() {} }');
        $this->assertEquals('method', $this->getFirstClass($source)->methods()->first()?->name());
    }

    public function testNoVirtualMethod(): void
    {
        $source = $this->build('<?php /** @method stdClass foobar() */class Foobar {  }');
        $this->assertCount(0, $this->getFirstClass($source)->methods());
    }

    public function testMethodWithReturnType(): void
    {
        $source = $this->build('<?php class Foobar { public function method(): string {} }');
        $this->assertEquals('string', $this->getFirstMethodInFirstClass($source)->returnType()->__toString());
    }

    public function testMethodWithNullableReturnType(): void
    {
        $source = $this->build('<?php class Foobar { public function method(): ?string {} }');
        // TODO: Changed this from `?string`, not sure how it worked before
        $this->assertEquals('?string', $this->getFirstMethodInFirstClass($source)->returnType()->__toString());
    }

    public function testMethodProtected(): void
    {
        $source = $this->build('<?php class Foobar { protected function method() {} }');
        $this->assertEquals('protected', $this->getFirstMethodInFirstClass($source)->visibility());
    }

    public function testMethodWithParameter(): void
    {
        $source = $this->build('<?php class Foobar { public function method($param) {} }');
        $this->assertEquals('param', $this->getFirstMethodInFirstClass($source)->parameters()->first()?->name());
    }

    public function testMethodWithNullableParameter(): void
    {
        $source = $this->build('<?php class Foobar { public function method(?string $param) {} }');
        self::assertEquals('?string', (string)$this->getFirstMethodInFirstClass($source)->parameters()->first()?->type());
    }

    public function testMethodWithParameterByReference(): void
    {
        $source = $this->build('<?php class Foobar { public function method(&$param) {} }');
        $this->assertTrue($this->getFirstMethodInFirstClass($source)->parameters()->first()?->byReference());
    }

    public function testMethodWithTypedParameter(): void
    {
        $source = $this->build('<?php class Foobar { public function method(string $param) {} }');
        $this->assertEquals('string', (string) $this->getFirstMethodInFirstClass($source)->parameters()->first()?->type());
    }

    public function testMethodWithVariadicParameter(): void
    {
        $source = $this->build('<?php class Foobar { public function method(string ...$param) {} }');
        $this->assertEquals('string', (string) $this->getFirstMethodInFirstClass($source)->parameters()->first()?->type());
    }

    public function testMethodWithMissingParameterType(): void
    {
        $source = $this->build('<?php class Foobar { public function method(...$param) {} }');
        $this->assertEquals('', (string) $this->getFirstMethodInFirstClass($source)->parameters()->first()?->type());
    }

    public function testMethodWithAliasedParameter(): void
    {
        $source = $this->build('<?php use Foobar as Barfoo; class Foobar { public function method(Barfoo $param) {} }');
        $this->assertEquals('Barfoo', (string) $this->getFirstMethodInFirstClass($source)->parameters()->first()?->type());
    }

    public function testMethodWithDefaultValue(): void
    {
        $source = $this->build('<?php class Foobar { public function method($param = 1234) {} }');
        $this->assertEquals(1234, (string) $this->getFirstMethodInFirstClass($source)->parameters()->first()?->defaultValue()->value());
    }

    public function testMethodWithDefaultValueQuoted(): void
    {
        $source = $this->build('<?php class Foobar { public function method($param = "1234") {} }');
        $this->assertEquals('1234', (string) $this->getFirstMethodInFirstClass($source)->parameters()->first()?->defaultValue()->value());
    }

    public function testStaticMethod(): void
    {
        $source = $this->build('<?php class Foobar { public static function method($param = "1234") {} }');
        $this->assertTrue($this->getFirstMethodInFirstClass($source)->isStatic());
    }

    public function testClassWhichExtendsClassWithMethods(): void
    {
        $source = $this->build(
            <<<'EOT'
                <?php
                class Foobar
                {
                    protected $bar;

                    public function method()
                    {
                    }
                }

                class BarBar extends Foobar
                {
                }
                EOT
        );
        $this->assertCount(0, $source->classes()->get('BarBar')->methods());
        $this->assertCount(0, $source->classes()->get('BarBar')->properties());
    }

    public function testInterface(): void
    {
        $source = $this->build('<?php interface Foobar {}');
        $firstInterface = $source->interfaces()->first();
        $this->assertNotNull($firstInterface);
        $this->assertEquals('Foobar', (string) $firstInterface->name());
    }

    public function testInterfaceWithMethod(): void
    {
        $source = $this->build('<?php interface Foobar { public function hello(World $world); }');
        $firstInterface = $source->interfaces()->first();
        $this->assertNotNull($firstInterface);
        $this->assertEquals('hello', (string) $firstInterface->methods()->get('hello')->name());
    }

    public function testInterfaceWithMethodParameters(): void
    {
        $source = $this->build('<?php interface Foobar { public function hello(World $world, string $bar, $foo); }');
        $firstInterface = $source->interfaces()->first();
        $this->assertNotNull($firstInterface);
        $this->assertEquals('hello', (string) $firstInterface->methods()->get('hello')->name());
        $this->assertEquals('world', (string) $firstInterface->methods()->get('hello')->parameters()->first()?->name());
        $this->assertEquals('foo', (string) $firstInterface->methods()->get('hello')->parameters()->get('foo')->name());
    }

    public function testDoesNotBuildPHP8PromotedProperties(): void
    {
        $source = $this->build('<?php class Foobar { function __construct(private $foobar){}}');
        self::assertEquals(0, $this->getFirstClass($source)->properties()->count());
    }

    private function getFirstClass(SourceCode $sourceCode): ClassPrototype
    {
        $class = $sourceCode->classes()->first();
        self::assertNotNull($class);
        return $class;
    }

    private function getFirstMethodInFirstClass(SourceCode $sourceCode): Method
    {
        $method = $this->getFirstClass($sourceCode)->methods()->first();
        self::assertNotNull($method);

        return $method;
    }

    private function build(string $source): SourceCode
    {
        $reflector = ReflectorBuilder::create()
            ->addMemberProvider(new DocblockMemberProvider())
            ->addSource($source)->build();

        $worseFactory = new WorseBuilderFactory($reflector);
        return $worseFactory->fromSource($source)->build();
    }
}
