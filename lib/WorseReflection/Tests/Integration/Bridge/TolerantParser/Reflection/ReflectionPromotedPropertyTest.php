<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use PHPUnit\Framework\Attributes\DataProvider;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Closure;
use Generator;

class ReflectionPromotedPropertyTest extends IntegrationTestCase
{
    #[DataProvider('provideConsturctorPropertyPromotion')]
    public function testReflectProperty(string $source, string $class, Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class->properties());
    }

    public function provideConsturctorPropertyPromotion(): Generator
    {
        yield 'Typed properties' => [
                <<<'EOT'
                    <?php

                    namespace Test;

                    class Barfoo
                    {
                        public function __construct(
                            private string $foobar
                            private int $barfoo,
                            private string|int $baz
                        ) {}
                    }
                    EOT
                ,
                'Test\Barfoo',
                function (ReflectionPropertyCollection $properties): void {
                    $this->assertTrue($properties->get('foobar')->isPromoted());
                    $this->assertEquals(
                        TypeFactory::string(),
                        $properties->get('foobar')->type()
                    );
                    $this->assertEquals(Visibility::private(), $properties->get('foobar')->visibility());
                    $this->assertEquals(
                        TypeFactory::int(),
                        $properties->get('barfoo')->type()
                    );
                    $this->assertEquals(
                        TypeFactory::union(
                            TypeFactory::string(),
                            TypeFactory::int(),
                        ),
                        $properties->get('baz')->inferredType()
                    );
                },
            ];

        yield 'Nullable' => [
                '<?php class Barfoo { public function __construct(private ?string $foobar){}}',

                'Barfoo',
                function (ReflectionPropertyCollection $properties): void {
                    $this->assertEquals(
                        TypeFactory::fromString('?string'),
                        $properties->get('foobar')->type()
                    );
                },
            ];

        yield 'No types' => [
                '<?php class Barfoo { public function __construct(private $foobar){}}',
                'Barfoo',
                function (ReflectionPropertyCollection $properties): void {
                    $this->assertEquals(
                        TypeFactory::undefined(),
                        $properties->get('foobar')->type()
                    );
                },
            ];

        yield 'With docblock' => [
            <<<'EOT'
                <?php class Barfoo {
                    public function __construct(
                        /** @var Foobar */
                        private $foobar
                    ) {}
                }
                EOT,
                'Barfoo',
                function (ReflectionPropertyCollection $properties): void {
                    $this->assertEquals(
                        'Foobar',
                        $properties->get('foobar')->inferredType()->__toString(),
                    );
                },
            ];
    }
}
