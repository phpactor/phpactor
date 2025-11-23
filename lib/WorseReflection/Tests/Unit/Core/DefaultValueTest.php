<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\DefaultValue;

class DefaultValueTest extends TestCase
{
    #[TestDox('It creates an undefined default value.')]
    public function testNone(): void
    {
        $value = DefaultValue::undefined();
        $this->assertFalse($value->isDefined());
    }

    #[TestDox('It represents a value')]
    public function testValue(): void
    {
        $value = DefaultValue::fromValue(42);
        $this->assertEquals(42, $value->value());
    }
}
