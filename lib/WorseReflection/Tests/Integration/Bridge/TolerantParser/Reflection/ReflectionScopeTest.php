<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Generator;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\NameImports;
use Closure;

class ReflectionScopeTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideScope
     */
    public function testScope(string $source, string $class, Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class);
    }

    public function provideScope(): Generator
    {
        yield 'Returns imported classes' => [
            <<<'EOT'
                <?php

                use Foobar\Barfoo;
                use Barfoo\Foobaz as Carzatz;

                class Class2
                {
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertEquals(NameImports::fromNames([
                    'Barfoo' => Name::fromString('Foobar\\Barfoo'),
                    'Carzatz' => Name::fromString('Barfoo\\Foobaz'),
                ]), $class->scope()->nameImports());
            },
        ];

        yield 'Returns local name' => [
            <<<'EOT'
                <?php

                use Foobar\Barfoo;

                class Class2
                {
                }

                EOT
        ,
            'Class2',
            function (ReflectionClass $class): void {
                $this->assertEquals(
                    Name::fromString('Barfoo'),
                    $class->scope()->resolveLocalName(Name::fromString('Foobar\Barfoo'))
                );
            },
        ];
    }
}
