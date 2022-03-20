<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Closure;
use Generator;

class DeclaredMemberTypeResolverTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideResolveTypes
     */
    public function testResolveTypes(string $source, string $class, Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClass(ClassName::fromString($class));
        $assertion($class->properties()->get('p'));
    }

    public function provideResolveTypes(): Generator
    {
        yield 'union type' => [
            '<?php class C { private int|string $p; }',
                'C',
                function (ReflectionProperty $property): void {
                    $this->assertEquals(Types::fromTypes([
                        TypeFactory::int(),
                        TypeFactory::string(),
                    ]), $property->inferredTypes());
                },
        ];

        yield 'union type with FQN' => [
            '<?php class C { private int|Foobar|Baz $p; }',
                'C',
                function (ReflectionProperty $property): void {
                    $this->assertEquals('int|Foobar|Baz', $property->inferredTypes()->__toString());
                },
        ];
    }
}
