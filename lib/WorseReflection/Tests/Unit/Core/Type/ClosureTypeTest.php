<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClosureType;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\ReflectorBuilder;

class ClosureTypeTest extends TestCase
{
    public function testAllTypes(): void
    {
        $reflector = ReflectorBuilder::create()->build();
        $type = new ClosureType($reflector, [
            TypeFactory::string(),
            TypeFactory::int(),
        ], TypeFactory::string());

        self::assertEquals(new Types([
            TypeFactory::reflectedClass($reflector, ClassName::fromString('Closure')),
            TypeFactory::string(),
            TypeFactory::int(),
            TypeFactory::string(),
        ]), $type->allTypes());
    }
}
