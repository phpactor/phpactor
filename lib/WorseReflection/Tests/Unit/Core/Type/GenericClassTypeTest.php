<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\ReflectorBuilder;

class GenericClassTypeTest extends TestCase
{
    public function testAllTypes(): void
    {
        $reflector = ReflectorBuilder::create()->build();
        $type = new GenericClassType($reflector, ClassName::fromString('Foo'), [
            TypeFactory::string(),
            TypeFactory::int(),
        ]);

        self::assertEquals(new Types([
            TypeFactory::reflectedClass($reflector, ClassName::fromString('Foo')),
            TypeFactory::string(),
            TypeFactory::int(),
        ]), $type->allTypes());
    }

    public function testAcceptsUnion(): void
    {
        $reflector = ReflectorBuilder::create()->addSource('<?php class Foo{} class A{} class B extends A{} class C extends A{}')->build();
        $type1 = new GenericClassType($reflector, ClassName::fromString('Foo'), [
            TypeFactory::reflectedClass($reflector, ClassName::fromString('A'))
        ]);

        $type2 = new GenericClassType($reflector, ClassName::fromString('Foo'), [
            TypeFactory::union(
                TypeFactory::reflectedClass($reflector, ClassName::fromString('B')),
                TypeFactory::reflectedClass($reflector, ClassName::fromString('C'))
            )
        ]);

        self::assertTrue($type1->accepts($type2)->isTrue());
    }
}
