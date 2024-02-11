<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Reflection\ReflectionEnumCase;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type\EnumBackedCaseType;
use Phpactor\WorseReflection\Core\Type\EnumCaseType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Closure;

class ReflectionEnumTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionEnum
     */
    public function testReflectEnum(string $source, string $class, Closure $assertion): void
    {
        if (!defined('T_ENUM')) {
            $this->markTestSkipped('PHP 8.1');
            return;
        }
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class);
    }

    public function provideReflectionEnum()
    {
        yield 'It reflects a enum' => [
                <<<'EOT'
                                    <?php

                                    enum Barfoo
                                    {
                                    }
                    EOT
        ,
        'Barfoo',
        function ($class): void {
            $this->assertEquals('Barfoo', (string) $class->name()->short());
            $this->assertInstanceOf(ReflectionEnum::class, $class);
            $this->assertTrue($class->isEnum());
        },
            ];
        yield 'It reflect enum methods' => [
        <<<'EOT'
                            <?php

                            enum Barfoo
                            {
                                public function foobar()
                                {
                                }
                            }
            EOT
        ,
        'Barfoo',
        function ($class): void {
            $this->assertEquals('Barfoo', (string) $class->name()->short());
            $this->assertEquals(['foobar', 'cases'], $class->methods()->keys());
        },
    ];
        yield 'Returns all members' => [
        <<<'EOT'
                        <?php

                        enum Enum1
                        {
                            case FOOBAR;
                            private $foovar;
                            private function foobar() {}
                        }

            EOT
        ,
        'Enum1',
        function (ReflectionEnum $class): void {
            $this->assertCount(4, $class->members());
            $this->assertInstanceOf(ReflectionEnumCase::class, $class->members()->get('FOOBAR'));
            $this->assertInstanceOf(ReflectionMethod::class, $class->members()->get('cases'));
        },
        ];

        yield 'Return case' => [
        <<<'EOT'
                            <?php

                            enum Enum1
                            {
                                case FOOBAR;
                            }

            EOT
            ,
            'Enum1',
            function (ReflectionEnum $class): void {
                $case = $class->cases()->get('FOOBAR');
                self::assertEquals('FOOBAR', $case->name());
                self::assertEquals('enum(Enum1::FOOBAR)', $case->type()->__toString());
                self::assertInstanceOf(MissingType::class, $case->value());
                self::assertInstanceOf(EnumCaseType::class, $case->type());
                self::assertEquals('FOOBAR', $case->name());
                self::assertFalse($class->isBacked());
            },
        ];
        yield 'Return backed case' => [
        <<<'EOT'
                            <?php

                            enum Enum1: string
                            {
                                case FOOBAR = 'FOO';
                            }

            EOT
            ,
            'Enum1',
            function (ReflectionEnum $class): void {
                $case = $class->cases()->get('FOOBAR');
                self::assertEquals('FOOBAR', $case->name());
                self::assertEquals('"FOO"', $case->value()->__toString());
                self::assertEquals('enum(Enum1::FOOBAR)', $case->type()->__toString());
                self::assertInstanceOf(EnumBackedCaseType::class, $case->type());
                self::assertTrue($class->isBacked());
                self::assertEquals('string', $class->backedType());
            },
        ];
        yield 'Return backed case with const' => [
        <<<'EOT'
                            <?php

                            enum Enum1: string
                            {
                                public const BAR = 'BAR';
                                case FOOBAR = self::BAR;
                            }

            EOT
            ,
            'Enum1',
            function (ReflectionEnum $class): void {
                $case = $class->cases()->get('FOOBAR');
                self::assertEquals('FOOBAR', $case->name());
                self::assertEquals('"BAR"', $case->value()->__toString());
                self::assertEquals('enum(Enum1::FOOBAR)', $case->type()->__toString());
                self::assertInstanceOf(EnumBackedCaseType::class, $case->type());
                self::assertTrue($class->isBacked());
                self::assertEquals('string', $class->backedType());
                $const = $class->constants()->get('BAR');
                self::assertEquals('BAR', $const->value());
            },
        ];
        yield 'Return backed methods' => [
        <<<'EOT'
                            <?php

                            interface BackedEnum {
                                public static function from(int|string $value): static;
                                public static function tryFrom(int|string $value): ?static;
                                public static function cases(): array;
                            }

                            enum Enum1:string
                            {
                            }

            EOT
            ,
            'Enum1',
            function (ReflectionEnum $class): void {
                $method = $class->methods()->get('from');
                self::assertTrue($class->methods()->has('cases'));
                self::assertEquals('Enum1', $method->returnType()->__toString());
            },
        ];
    }
}
