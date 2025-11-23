<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Adapter\WorseReflection\TypeRenderer;

use Generator;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer81;
use Phpactor\WorseReflection\Core\Type\FalseType;
use Phpactor\WorseReflection\Core\Type\IntersectionType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\StringType;

class WorseTypeRenderer81Test extends TypeRendererTestCase
{
    public static function provideType(): Generator
    {
        yield [
            new FalseType(),
            'bool',
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
            new IntersectionType(new StringType(), new FalseType()),
            'string&bool',
        ];
    }

    protected function createRenderer(): WorseTypeRenderer
    {
        return new WorseTypeRenderer81();
    }
}
