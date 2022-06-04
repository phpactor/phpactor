<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Adapter\WorseReflection\TypeRenderer;

use Generator;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer74;
use Phpactor\WorseReflection\Core\Type\CallableType;
use Phpactor\WorseReflection\Core\Type\ClosureType;
use Phpactor\WorseReflection\Core\Type\FalseType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Type\UnionType;

class WorseTypeRenderer74Test extends TypeRendererTestCase
{
    public function provideType(): Generator
    {
        yield [
            new FalseType(),
            'bool',
        ];
        yield [
            new MixedType(),
            '',
        ];
        yield [
            new StringType(),
            'string',
        ];
        yield [
            new ClosureType(),
            'Closure',
        ];
        yield [
            new CallableType(),
            'callable',
        ];
        yield [
            new UnionType(new StringType(), new FalseType()),
            '',
        ];
    }

    protected function createRenderer(): WorseTypeRenderer
    {
        return new WorseTypeRenderer74();
    }
}
