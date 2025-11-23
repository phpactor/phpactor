<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use Closure;
use Generator;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReflection\Tests\Assert\TrinaryAssert;

class ReflectedClassTypeTest extends TestCase
{
    use TrinaryAssert;

    #[DataProvider('provideAccepts')]
    public function testAccepts(Type $type, Closure $closure): void
    {
        $closure($type);
    }

    public function provideAccepts(): Generator
    {
        yield 'accepts class which is it' => [
            $this->createType(
                '<?php class Bar{};',
                'Bar'
            ),
            function (Type $type): void {
                self::assertTrue($type->accepts(TypeFactory::class('Bar'))->isTrue());
            }
        ];
        yield 'accepts class which extends it' => [
            $this->createType(
                '<?php class Bar{}; class Foobar extends Bar {};',
                'Bar'
            ),
            function (Type $type): void {
                self::assertTrue($type->accepts(TypeFactory::class('Foobar'))->isTrue());
            }
        ];
        yield 'rejects class which implements it' => [
            $this->createType(
                '<?php interface Bar{}; class Foobar implements Bar {};',
                'Bar'
            ),
            function (Type $type): void {
                self::assertTrue($type->accepts(TypeFactory::class('Foobar'))->isTrue());
            }
        ];
        yield 'rejects class which is not it' => [
            $this->createType(
                '<?php class Bar{}; class Foobar {};',
                'Bar'
            ),
            function (Type $type): void {
                self::assertTrue($type->accepts(TypeFactory::class('Foobar'))->isFalse());
            }
        ];

        yield 'interface accepts class which implements it' => [
            $this->createType(
                '<?php interface Bar{}; class Foobar implements Bar {};',
                'Bar'
            ),
            function (Type $type): void {
                self::assertTrue($type->accepts(TypeFactory::class('Foobar'))->isTrue());
            }
        ];
    }

    public function testInstanceOf(): void
    {
        // is extends
        self::assertTrinaryTrue($this->createType(
            '<?php class Bar{}; class Foobar extends Bar {};',
            'Foobar'
        )->instanceof(TypeFactory::class('Bar')));

        // is not instance of
        self::assertTrinaryFalse($this->createType(
            '<?php class Bar{}; class Foobar extends Bar {};',
            'Foobar'
        )->instanceof(TypeFactory::class('Baz')));

        // is possibly instance of because we can't reflect the class
        self::assertTrinaryMaybe($this->createType(
            '',
            'Foobar'
        )->instanceof(TypeFactory::class('Baz')));
    }

    private function createType(string $source, string $name): ReflectedClassType
    {
        return new ReflectedClassType(
            ReflectorBuilder::create()->addSource($source)->build(),
            ClassName::fromUnknown($name)
        );
    }
}
