<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Adapter\WorseReflection\TypeRenderer;

use Generator;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer82;
use Phpactor\WorseReflection\Core\Type\FalseType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\ObjectType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Type\UnionType;

class WorseTypeRenderer82Test extends TypeRendererTestCase
{
    public function provideType(): Generator
    {
        yield [
            new FalseType(),
            'false',
        ];
        yield [
            new MixedType(),
            'mixed',
        ];
        yield [
            new StringType(),
            'string',
        ];
        yield [
            new UnionType(new StringType(), new FalseType()),
            'string|false',
        ];
        yield [
            new UnionType(new StringType(), new ObjectType()),
            'string|object',
        ];
    }

    protected function createRenderer(): WorseTypeRenderer
    {
        return new WorseTypeRenderer82();
    }
}
