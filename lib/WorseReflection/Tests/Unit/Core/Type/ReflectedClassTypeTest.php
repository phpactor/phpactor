<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReflection\Tests\Assert\TrinaryAssert;

class ReflectedClassTypeTest extends TestCase
{
    use TrinaryAssert;

    public function testInstanceOf(): void
    {
        // is instance of
        self::assertTrinaryTrue($this->createType(
            '<?php class Bar{}; class Foobar extends Bar {};',
            'Foobar'
        )->instanceOf(ClassName::fromUnknown('Bar')));

        // is not instance of
        self::assertTrinaryFalse($this->createType(
            '<?php class Bar{}; class Foobar extends Bar {};',
            'Foobar'
        )->instanceOf(ClassName::fromUnknown('Baz')));

        // is possibly instance of because we can't reflect the class
        self::assertTrinaryMaybe($this->createType(
            '',
            'Foobar'
        )->instanceOf(ClassName::fromUnknown('Baz')));
    }

    private function createType(string $source, string $name): ReflectedClassType
    {
        return new ReflectedClassType(
            ReflectorBuilder::create()->addSource($source)->build(),
            ClassName::fromUnknown($name)
        );
    }
}
