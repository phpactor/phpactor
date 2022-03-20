<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Closure;

class ReflectionTraitTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionTrait
     */
    public function testReflectTrait(string $source, string $class, Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class);
    }

    public function provideReflectionTrait()
    {
        return [
            'It reflects a trait' => [
                <<<'EOT'
                    <?php

                    trait Barfoo
                    {
                    }
                    EOT
                ,
                'Barfoo',
                function ($class): void {
                    $this->assertEquals('Barfoo', (string) $class->name()->short());
                    $this->assertInstanceOf(ReflectionTrait::class, $class);
                    $this->assertTrue($class->isTrait());
                },
            ],
            'It reflects a classes traits' => [
                <<<'EOT'
                    <?php
                    trait Barfoo
                    {
                    }

                    trait Bazbar
                    {
                    }

                    class Foobar
                    {
                        use Barfoo;
                        use Bazbar;
                    }
                    EOT
                ,
                'Foobar',
                function ($class): void {
                    $traits = $class->traits();
                    $this->assertCount(2, $traits);
                    $trait = $traits[0];
                    $this->assertInstanceOf(ReflectionTrait::class, $trait);
                },
            ],
            'It reflect trait methods' => [
                <<<'EOT'
                    <?php

                    trait Barfoo
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
                    $this->assertEquals(['foobar'], $class->methods()->keys());
                },
            ],
            'Trait properties' => [
                <<<'EOT'
                    <?php

                    trait Int1
                    {
                        protected $foobar;
                        protected $barfoo;
                    }
                    EOT
                ,
                'Int1',
                function ($class): void {
                    $this->assertCount(2, $class->properties());
                    $this->assertEquals('foobar', $class->properties()->first()->name());
                },
            ],
            'Ignores inherit docs on trait' => [
                <<<'EOT'
                    <?php

                    trait Int1
                    {
                        /**
                         * {@inheritDoc()
                         */
                        public function foo()
                        {
                        }
                    }
                    EOT
                ,
                'Int1',
                function (ReflectionTrait $class): void {
                    $this->assertEquals(TypeFactory::unknown(), $class->methods()->first()->inferredReturnTypes()->best());
                },
            ],
            'instanceof' => [
                <<<'EOT'
                    <?php
                    trait Trait1
                    {
                    }
                    EOT
                ,
                'Trait1',
                function ($class): void {
                    $this->assertTrue($class->isInstanceOf(ClassName::fromString('Trait1')));
                    $this->assertFalse($class->isInstanceOf(ClassName::fromString('Interface1')));
                },
            ],
        ];

        yield 'Returns all members' => [
            <<<'EOT'
                <?php

                trait Class1
                {
                    private $foovar;
                    private function foobar() {}
                }

                EOT
        ,
            'Class1',
            function (ReflectionTrait $class): void {
                $this->assertCount(2, $class->members());
                $this->assertTrue($class->members()->has('foovar'));
                $this->assertTrue($class->members()->has('foobar'));
            },
        ];
    }
}
