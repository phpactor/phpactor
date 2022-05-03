<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\LocalAssignments;
use Phpactor\WorseReflection\Core\Inference\TypeAssertion;
use Phpactor\WorseReflection\Core\Inference\TypeAssertions;
use Phpactor\WorseReflection\Core\Inference\TypeCombinator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

class TypeAssertionsTest extends TestCase
{
    /**
     * @dataProvider provideOr
     */
    public function testOr(Type $type, TypeAssertion $a, TypeAssertion $b, Type $expected, Type $negated): void
    {
        $assertions = new TypeAssertions([$a]);
        $assertions = $assertions->or(new TypeAssertions([$b]));
        $assertion = $assertions->variables()->firstForName('foo');

        self::assertEquals($expected->__toString(), $assertion->apply($type)->__toString());
        self::assertEquals($negated->__toString(), $assertion->negate()->apply($type)->__toString());

    }

    public function provideOr(): Generator
    {
        yield [
            TypeFactory::mixed(),
            TypeAssertion::variable(
                'foo',
                0,
                fn (Type $t) => TypeFactory::string(),
                fn (Type $t) => TypeFactory::null(),
            ),
            TypeAssertion::variable(
                'foo',
                0,
                fn (Type $t) => TypeFactory::bool(),
                fn (Type $t) => TypeFactory::null()
            ),
            TypeFactory::union(
                TypeFactory::string(),
                TypeFactory::bool()
            ),
            TypeFactory::null(),
        ];
    }
}
