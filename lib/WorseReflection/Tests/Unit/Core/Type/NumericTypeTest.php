<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\WorseReflection\Core\Type\IntLiteralType;
use Phpactor\WorseReflection\Core\Type\IntType;

class NumericTypeTest extends TestCase
{
    public function testDivisionByZero(): void
    {
        self::assertEquals(0, (new IntLiteralType(1))->divide(new IntLiteralType(0))->value());
        self::assertEquals(0, (new IntLiteralType(0))->divide(new IntLiteralType(1))->value());
    }

    public function testDivisionByNonLiteral(): void
    {
        self::assertEquals(1, (new IntLiteralType(1))->divide(new IntType())->value());
    }
}
