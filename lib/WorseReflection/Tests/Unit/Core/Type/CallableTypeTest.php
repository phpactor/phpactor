<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\CallableType;

class CallableTypeTest extends TestCase
{
    public function testToStringWithNoType(): void
    {
        self::assertEquals('callable()', TypeFactory::callable()->__toString());
    }

    public function testToStringWithReturnType(): void
    {
        self::assertEquals('callable(): string', (new CallableType([], TypeFactory::string()))->__toString());
    }
}
