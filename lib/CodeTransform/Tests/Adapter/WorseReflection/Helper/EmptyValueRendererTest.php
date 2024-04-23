<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Helper;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Adapter\WorseReflection\Helper\EmptyValueRenderer;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\ReflectorBuilder;

class EmptyValueRendererTest extends TestCase
{
    public function testEnum(): void
    {
        $reflector = ReflectorBuilder::create()->addSource('<?php enum Borders {case ALL;}')->build();
        $default = (new EmptyValueRenderer())->render(TypeFactory::reflectedClass($reflector, 'Borders'));
        self::assertEquals('Borders::ALL', $default);
    }
    public function testEnumNoCases(): void
    {
        $reflector = ReflectorBuilder::create()->addSource('<?php enum Borders {}')->build();
        $default = (new EmptyValueRenderer())->render(TypeFactory::reflectedClass($reflector, 'Borders'));
        self::assertEquals('/** enum `Borders` has no cases */', $default);
    }
}
