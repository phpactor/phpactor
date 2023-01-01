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
}
