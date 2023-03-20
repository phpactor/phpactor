<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TestUtils\ExtractOffset;
use Closure;

class ReflectionMethodCallTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionMethod
     */
    public function testReflectMethodCall(string $source, array $frame, Closure $assertion): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $reflection = $this->createReflector($source)->reflectMethodCall($source, $offset);
        $assertion($reflection);
    }

    public function provideReflectionMethod()
    {
        return [
            'It reflects the method name' => [
                <<<'EOT'
                    <?php

                    $foo->b<>ar();
                    EOT
                , [
                ],
                function (ReflectionMethodCall $method): void {
                    $this->assertEquals('bar', $method->name());
                },
            ],
            'It reflects a method' => [
                <<<'EOT'
                    <?php

                    $foo->b<>ar();
                    EOT
                , [
                ],
                function (ReflectionMethodCall $method): void {
                    $this->assertEquals('bar', $method->name());
                },
            ],
            'It returns the position' => [
                <<<'EOT'
                    <?php

                    $foo->foo->b<>ar();
                    EOT
                , [
                ],
                function (ReflectionMethodCall $method): void {
                    $this->assertInstanceOf(ByteOffsetRange::class, $method->position());
                    $this->assertEquals(7, $method->position()->start()->toInt());
                    $this->assertEquals(21, $method->position()->end()->toInt());
                },
            ],
            'It returns the containing class' => [
                <<<'EOT'
                    <?php

                    class BBB
                    {
                    }

                    class AAA
                    {
                        public function foo(): BBB
                        {
                        }
                    }

                    $foo = new AAA;
                    $foo->foo()->b<>ar();

                    EOT
                , [
                ],
                function (ReflectionMethodCall $method): void {
                    $this->assertInstanceOf(ByteOffsetRange::class, $method->position());
                    $this->assertEquals(ClassName::fromString('BBB'), $method->class()->name());
                },
            ],
            'It returns if the call is static' => [
                <<<'EOT'
                    <?php

                    class AAA
                    {
                    }

                    AAA::b<>ar();

                    EOT
                , [
                ],
                function (ReflectionMethodCall $method): void {
                    $this->assertInstanceOf(ByteOffsetRange::class, $method->position());
                    $this->assertTrue($method->isStatic());
                    $this->assertEquals(ClassName::fromString('AAA'), $method->class()->name());
                },
            ],
            'It has arguments' => [
                <<<'EOT'
                    <?php

                    class AAA
                    {
                    }

                    $a = 1;
                    $foo = new AAA();
                    $foo->b<>ar($a);

                    EOT
                , [
                ],
                function (ReflectionMethodCall $method): void {
                    $this->assertInstanceOf(ByteOffsetRange::class, $method->position());
                    $this->assertEquals('a', $method->arguments()->first()->guessName());
                },
            ],
        ];
    }
}
