<?php

namespace Phpactor\Extension\Prophecy\Tests\Integration\WorseReflection;

use Phpactor\Extension\Prophecy\WorseReflection\ProphecyMemberContextResolver;
use Phpactor\Extension\Prophecy\WorseReflection\ProphecyStubLocator;
use Phpactor\Extension\Symfony\Tests\IntegrationTestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Inference\Walker\TestAssertWalker;
use Phpactor\WorseReflection\ReflectorBuilder;

class ProphecyMemberContextResolverTest extends IntegrationTestCase
{
    public function testProphesize(): void
    {
        $this->resolve(
            <<<'EOT'
                <?php
                class Hello {
                    public function bar(): string
                    {
                    }
                }

                class Foobar {
                    public function prophesize(string $class): \Prophecy\Prophecy\ObjectProphecy
                    {
                    }
                }

                $prophet = (new Foobar())->prophesize(Hello::class);

                wrAssertType('Prophecy\Prophecy\ObjectProphecy<Hello>', $prophet);
                wrAssertType('Prophecy\Prophecy\MethodProphecy<Hello>', $prophet->bar());
                wrAssertType('Prophecy\Prophecy\MethodProphecy<Hello>', $prophet->bar()->will());
                wrAssertType('Prophecy\Prophecy\ObjectProphecy<Hello>', $prophet->bar()->getObjectProphecy());
                wrAssertType('Prophecy\Prophecy\MethodProphecy<Hello>', $prophet->bar()->getObjectProphecy()->bar());
                wrAssertType('Prophecy\Prophecy\MethodProphecy<Hello>', $prophet->bar()->willReturn('')->getObjectProphecy()->bar());
                wrAssertType('string', $prophet->bar()->getMethodName());
                wrAssertType('Hello', $prophet->reveal());
                EOT
            ,
        );
    }
    public function testMethodProphecy(): void
    {
        $this->resolve(
            <<<'EOT'
                <?php
                class Hello {
                    public function bar(): string
                    {
                    }
                }
                class Foobar {
                    public function prophesize(string $class): \Prophecy\Prophecy\ObjectProphecy {}
                }
                $prophet = (new Foobar())->prophesize(Hello::class);

                wrAssertType('Prophecy\Prophecy\MethodProphecy<Hello>', $prophet->bar()->willReturn(''));
                wrAssertType('Prophecy\Prophecy\ObjectProphecy<Hello>', $prophet->bar()->willReturn('')->getObjectProphecy());
                wrAssertType('Prophecy\Prophecy\MethodProphecy<Hello>', $prophet->bar()->willReturn('')->getObjectProphecy()->bar());
                EOT
            ,
        );
    }

    public function testProphesizeFromProperty(): void
    {
        $this->resolve(
            <<<'EOT'
                <?php
                use Prophecy\Prophecy\ObjectProphecy;
                class Hello {
                    public function bar(): string
                    {
                    }
                }

                class TestCase {
                    /**
                     * @var ObjectProphecy<Hello>
                     */
                    private $hello;
                   
                    public function hello(): void
                    {
                        wrAssertType('Prophecy\Prophecy\ObjectProphecy<Hello>', $this->hello);
                        wrAssertType('Prophecy\Prophecy\MethodProphecy<Hello>', $this->hello->bar());
                    }
                }
                EOT
            ,
        );
    }

    public function testProphesizeFromMethod(): void
    {
        $this->resolve(
            <<<'EOT'
                <?php
                use Prophecy\Prophecy\ObjectProphecy;
                class Hello {
                    public function bar(): string
                    {
                    }
                }

                class TestCase {
                    /**
                     * @return ObjectProphecy<Hello>
                     */
                    public function foobar(): ObjectProphecy
                   
                    public function hello(): void
                    {
                        wrAssertType('Prophecy\Prophecy\ObjectProphecy<Hello>', $this->foobar());
                    }
                }
                EOT
            ,
        );
    }

    public function testProphesizeSelfInATrait(): void
    {
        $this->resolve(
            <<<'EOT'
                <?php

                trait StorageManagerHelperTrait
                {
                    abstract protected function prophesize(?string $classOrInterface = null): ObjectProphecy;

                    protected function getStorageManager(): ObjectProphecy
                    {
                        return $this->prophesize(self::class);
                    }
                }

                class TestCase {
                    use StorageManagerHelperTrait;
                    /**
                     * @return ObjectProphecy<Hello>
                     */
                    public function foobar(): ObjectProphecy
                   
                    public function hello(): void
                    {
                        wrAssertType('ObjectProphecy', $this->getStorageManager());
                    }
                }
                EOT
            ,
        );
    }

    public function testProphesizeExtends(): void
    {
        $this->resolve(<<<'EOT'
            <?php
            class SomeClass {
                public function someFunction(): void {}
            }
            class ExtensionClass {
                public function otherFunction(): void {}
            }

            class TestCase {
                /** @var \Prophecy\Prophecy\ObjectProphecy<SomeClass> */
                private $prophet;

                public function doTest(): void
                {
                    wrAssertType(
                        'Prophecy\Prophecy\ObjectProphecy<SomeClass|ExtensionClass>',
                        $this->prophet->willExtend(ExtensionClass::class)
                    );
                }
            }
            EOT);
    }

    public function testProphesizeImplements(): void
    {
        $this->resolve(<<<'EOT'
            <?php
            class SomeClass {
                public function someFunction(): void {}
            }
            interface Extension {
                public function otherFunction(): void {}
            }

            class TestCase {
                /** @var \Prophecy\Prophecy\ObjectProphecy<SomeClass> */
                private $prophet;

                public function doTest(): void
                {
                    wrAssertType(
                        'Prophecy\Prophecy\ObjectProphecy<SomeClass|Extension>',
                        $this->prophet->willImplement(Extension::class)
                    );
                }
            }
            EOT);
    }

    public function resolve(string $sourceCode): void
    {
        $sourceCode = TextDocumentBuilder::fromUnknown($sourceCode);
        $reflector = ReflectorBuilder::create()
            ->addFrameWalker(new TestAssertWalker($this))
            ->addLocator(new ProphecyStubLocator())
            ->addSource($sourceCode)
            ->addMemberContextResolver(new ProphecyMemberContextResolver())
            ->build();

        $reflector->reflectOffset($sourceCode, mb_strlen($sourceCode));
    }
}
