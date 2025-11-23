<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\UnionType;

class UnionTypeTest extends TestCase
{
    #[DataProvider('provideAccepts')]
    public function testAccepts(UnionType $union, Type $type, Trinary $accepts): void
    {
        self::assertEquals($accepts, $union->accepts($type));
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideAccepts(): Generator
    {
        yield [
            TypeFactory::union(TypeFactory::int(), TypeFactory::string()),
            TypeFactory::int(),
            Trinary::true(),
        ];
        yield [
            TypeFactory::union(TypeFactory::int(), TypeFactory::string()),
            TypeFactory::class('Foobar'),
            Trinary::false(),
        ];
        yield 'int literal maybe accepts int' => [
            TypeFactory::union(TypeFactory::intLiteral(12), TypeFactory::string()),
            TypeFactory::int(),
            Trinary::maybe(),
        ];
        yield 'string literal maybe string' => [
            TypeFactory::union(TypeFactory::stringLiteral('foo')),
            TypeFactory::string(),
            Trinary::maybe(),
        ];
        yield 'bool literal maybe bool' => [
            TypeFactory::union(TypeFactory::boolLiteral(true)),
            TypeFactory::bool(),
            Trinary::maybe(),
        ];
        yield 'float literal maybe float' => [
            TypeFactory::union(TypeFactory::floatLiteral(12.2)),
            TypeFactory::float(),
            Trinary::maybe(),
        ];
        yield 'boolean true is not empty' => [
            TypeFactory::union(TypeFactory::unionEmpty()),
            TypeFactory::boolLiteral(true),
            Trinary::false(),
        ];
    }
}
