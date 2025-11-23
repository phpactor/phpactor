<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\StringType;

class ArrayTypeTest extends TestCase
{
    #[DataProvider('provideToString')]
    public function testToString(ArrayType $type, string $expected): void
    {
        self::assertEquals($expected, $type->__toString());
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideToString(): Generator
    {
        yield [
                new ArrayType(new StringType()),
                'string[]',
            ];
        yield [
                new ArrayType(null, new StringType()),
                'string[]',
            ];
        yield [
                new ArrayType(new IntType(), new StringType()),
                'array<int,string>',
            ];
    }
}
