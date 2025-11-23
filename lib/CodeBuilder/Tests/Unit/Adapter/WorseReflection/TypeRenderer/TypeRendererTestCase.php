<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Adapter\WorseReflection\TypeRenderer;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer;
use Phpactor\WorseReflection\Core\Type;

abstract class TypeRendererTestCase extends TestCase
{
    #[DataProvider('provideType')]
    public function testRender(Type $type, string $expected): void
    {
        self::assertEquals($expected, ($this->createRenderer())->render($type));
    }

    abstract public static function provideType(): Generator;

    abstract protected function createRenderer(): WorseTypeRenderer;
}
