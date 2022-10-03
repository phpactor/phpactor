<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use Phpactor\WorseReflection\Core\Type\CallableType;
use Phpactor\WorseReflection\Core\TypeFactory;
use PHPUnit\Framework\TestCase;

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
