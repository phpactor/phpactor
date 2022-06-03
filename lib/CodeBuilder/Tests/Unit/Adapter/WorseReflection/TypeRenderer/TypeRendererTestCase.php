<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Adapter\WorseReflection\TypeRenderer;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer74;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\FalseType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\StringType;

abstract class TypeRendererTestCase extends TestCase
{
    /**
     * @dataProvider provideType
     */
    public function testRender(Type $type, string $expected): void
    {
        self::assertEquals($expected, ($this->createRenderer())->render($type));
    }

    abstract public function provideType(): Generator;

    abstract protected function createRenderer(): WorseTypeRenderer;
}

