<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Closure;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;

class ReflectionFunctionTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectsFunction
     */
    public function testReflects(string $source, string $functionName, Closure $assertion): void
    {
        $functions = $this->createReflector($source)->reflectFunctionsIn($source);
        $assertion($functions->get($functionName));
    }

    public function provideReflectsFunction()
    {
        yield 'single function with no params' => [
            <<<'EOT'
                <?php
                function hello()
                {
                }
                EOT
            , 'hello', function (ReflectionFunction $function): void {
                $this->assertEquals('hello', $function->name());
                $this->assertEquals(Position::fromStartAndEnd(6, 26), $function->position());
            }
        ];

        yield 'function\'s frame' => [
            <<<'EOT'
                <?php
                function hello()
                {
                    $hello = 'hello';
                }
                EOT
            , 'hello', function (ReflectionFunction $function): void {
                $this->assertCount(1, $function->frame()->locals());
            }
        ];

        yield 'the docblock' => [
            <<<'EOT'
                <?php
                /** Hello */
                function hello()
                {
                    $hello = 'hello';
                }
                EOT
            , 'hello', function (ReflectionFunction $function): void {
                $this->assertEquals('/** Hello */', trim($function->docblock()->raw()));
            }
        ];

        yield 'the declared scalar type' => [
            <<<'EOT'
                <?php
                function hello(): string {}
                EOT
            , 'hello', function (ReflectionFunction $function): void {
                $this->assertEquals('string', $function->type());
            }
        ];

        yield 'the declared class type' => [
            <<<'EOT'
                <?php
                use Foobar\Barfoo;
                function hello(): Barfoo {}
                EOT
            , 'hello', function (ReflectionFunction $function): void {
                $this->assertEquals('Foobar\Barfoo', $function->type());
            }
        ];

        yield 'unknown if nothing declared as type' => [
            <<<'EOT'
                <?php
                function hello() {}
                EOT
            , 'hello', function (ReflectionFunction $function): void {
                $this->assertEquals(TypeFactory::unknown(), $function->type());
            }
        ];

        yield 'type from docblock' => [
            <<<'EOT'
                <?php
                /**
                 * @return string
                 */
                function hello() {}
                EOT
            , 'hello', function (ReflectionFunction $function): void {
                $this->assertEquals(TypeFactory::string(), $function->inferredTypes()->best());
            }
        ];

        yield 'resolved type class from docblock' => [
            <<<'EOT'
                <?php
                namespace Bar;

                use Foo\Goodbye;

                /**
                 * @return Goodbye
                 */
                function hello() {}
                EOT
            , 'Bar\hello', function (ReflectionFunction $function): void {
                $this->assertEquals('Foo\Goodbye', $function->inferredTypes()->best()->__toString());
            }
        ];


        yield 'parameters' => [
                <<<'EOT'
                    <?php

                    namespace Bar;

                    function hello($foobar, Barfoo $barfoo, int $number)
                    {
                    }
                    EOT
        , 'Bar\hello', function (ReflectionFunction $function): void {
            $this->assertCount(3, $function->parameters());
            $this->assertEquals('Bar\Barfoo', $function->parameters()->get('barfoo')->inferredTypes()->best());
        },
        ];

        yield 'returns the source code' => [
                <<<'EOT'
                    <?php

                    namespace Bar;

                    function hello($foobar, Barfoo $barfoo, int $number)
                    {
                    }
                    EOT
        , 'Bar\hello', function (ReflectionFunction $function): void {
            $this->assertStringContainsString('function hello(', (string) $function->sourceCode());
        },
        ];
    }
}
