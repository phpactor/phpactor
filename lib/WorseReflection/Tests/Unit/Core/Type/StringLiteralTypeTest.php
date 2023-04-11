<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Type\StringLiteralType;

class StringLiteralTypeTest extends TestCase
{
    public function testTruncatesLongValue(): void
    {
        $value = str_repeat('a', 255);
        self::assertEquals(
            'aaa',
            substr((new StringLiteralType($value))->value(), -3)
        );
        $value = str_repeat('a', 356);
        self::assertEquals(
            '...',
            substr((new StringLiteralType($value))->value(), -3)
        );
    }
}
