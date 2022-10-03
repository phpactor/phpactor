<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Adapter\WorseReflection\TypeRenderer;

use Generator;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer;
use Phpactor\WorseReflection\Core\Type;
use PHPUnit\Framework\TestCase;

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
