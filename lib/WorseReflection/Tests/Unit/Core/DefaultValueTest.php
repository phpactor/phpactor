<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\DefaultValue;

class DefaultValueTest extends TestCase
{
    /**
     * @testdox It creates an undefined default value.
     */
    public function testNone(): void
    {
        $value = DefaultValue::undefined();
        $this->assertFalse($value->isDefined());
    }

    /**
     * @testdox It represents a value
     */
    public function testValue(): void
    {
        $value = DefaultValue::fromValue(42);
        $this->assertEquals(42, $value->value());
    }
}
