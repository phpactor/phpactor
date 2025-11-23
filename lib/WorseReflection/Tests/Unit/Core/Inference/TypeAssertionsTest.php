<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\TypeAssertion;
use Phpactor\WorseReflection\Core\Inference\TypeAssertions;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

class TypeAssertionsTest extends TestCase
{
    #[DataProvider('provideOr')]
    public function testOr(Type $type, TypeAssertion $a, TypeAssertion $b, Type $expected, Type $negated): void
    {
        $assertions = new TypeAssertions([$a]);
        $assertions = $assertions->or(new TypeAssertions([$b]));
        $assertion = $assertions->variables()->firstForName('foo');

        self::assertEquals($expected->__toString(), $assertion->apply($type)->__toString());
        self::assertEquals($negated->__toString(), $assertion->negate()->apply($type)->__toString());
    }

    public static function provideOr(): Generator
    {
        yield [
            TypeFactory::mixed(),

            // assert foo is STRING positively and NULL negatively
            TypeAssertion::variable(
                'foo',
                0,
                fn (Type $t) => $t->addType(TypeFactory::string()),
                fn (Type $t) => $t->addType(TypeFactory::null()),
            ),

            // assert foo is STRING positively and int NULL negatively
            TypeAssertion::variable(
                'foo',
                0,
                fn (Type $t) => $t->addType(TypeFactory::int()),
                fn (Type $t) => $t->addType(TypeFactory::float()),
            ),

            // it's either mixed, int or string
            TypeFactory::union(
                TypeFactory::mixed(),
                TypeFactory::string(),
                TypeFactory::int()
            ),

            // it's either mixed, int or string
            TypeFactory::union(
                TypeFactory::mixed(),
                TypeFactory::null(),
                TypeFactory::float()
            ),
        ];
    }

    #[DataProvider('provideAnd')]
    public function testAnd(Type $type, TypeAssertion $a, TypeAssertion $b, Type $expected, Type $negated): void
    {
        $assertions = new TypeAssertions([$a]);
        $assertions = $assertions->and(new TypeAssertions([$b]));
        $assertion = $assertions->variables()->firstForName('foo');

        self::assertEquals($expected->__toString(), $assertion->apply($type)->__toString());
        self::assertEquals($negated->__toString(), $assertion->negate()->apply($type)->__toString());
    }

    public static function provideAnd(): Generator
    {
        yield [
            TypeFactory::mixed(),

            // both assertions should be applied on positive
            TypeAssertion::variable(
                'foo',
                0,
                fn (Type $t) => $t->addType(TypeFactory::string()),
                fn (Type $t) => $t->addType(TypeFactory::null()),
            ),

            // both assertions should be applied on negative
            TypeAssertion::variable(
                'foo',
                0,
                fn (Type $t) => $t->addType(TypeFactory::int()),
                fn (Type $t) => $t->addType(TypeFactory::float()),
            ),

            TypeFactory::union(
                TypeFactory::mixed(),
                TypeFactory::string(),
                TypeFactory::int(),
            ),
            TypeFactory::union(
                TypeFactory::mixed(),
                TypeFactory::null(),
                TypeFactory::float(),
            ),
        ];
    }
}
