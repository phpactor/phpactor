<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Adapter\WorseReflection\TypeRenderer;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer74;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer81;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer82;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\FalseType;
use Phpactor\WorseReflection\Core\Type\IntersectionType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Type\UnionType;

class WorseTypeRenderer81Test extends TypeRendererTestCase
{
    public function provideType(): Generator
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

        //$this->foobar(file_get_contents('asd'));
    }
}

