<?php

namespace Phpactor\WorseReflection\Tests\Assert;

use Phpactor\WorseReflection\Core\Trinary;

trait TrinaryAssert
{
    public static function assertTrinaryTrue(Trinary $trinary): void
    {
        self::assertEquals(Trinary::true(), $trinary);
    }

    public static function assertTrinaryFalse(Trinary $trinary): void
    {
        self::assertEquals(Trinary::false(), $trinary);
    }

    public static function assertTrinaryMaybe(Trinary $trinary): void
    {
        self::assertEquals(Trinary::maybe(), $trinary);
    }
}
