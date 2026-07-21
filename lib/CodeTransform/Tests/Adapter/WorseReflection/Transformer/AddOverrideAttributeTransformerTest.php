<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Transformer;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\AddOverrideAttributeTransformer;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use function Amp\Promise\wait;

class AddOverrideAttributeTransformerTest extends WorseTestCase
{
    #[DataProvider('provideAddOverrideAttribute')]
    public function testAddOverrideAttribute(string $example, string $expected): void
    {
        $this->workspace()->put('Bag.php', '<?php class Bag { public function bar(): Boo {} }');
        $this->workspace()->put('Boo.php', '<?php class Boo{}');

        $source = SourceCode::fromString($example);
        $transformer = new AddOverrideAttributeTransformer($this->reflectorForWorkspace($example), '8.3');
        $transformed = wait($transformer->transform($source));
        $this->assertEquals($expected, (string) $transformed->apply($source));
    }

    /**
     * @return Generator<string,array{string,string}>
     */
    public static function provideAddOverrideAttribute(): Generator
    {
        yield 'It does nothing on source with no classes' => [
            <<<'EOT'
                <?php
                EOT
            ,
            <<<'EOT'
                <?php
                EOT
        ];

        yield 'It adds attribute to method overriding a parent method' => [
            <<<'EOT'
                <?php

                class ParentClass
                {
                    public function foo(): void
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    public function foo(): void
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class ParentClass
                {
                    public function foo(): void
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    #[\Override]
                    public function foo(): void
                    {
                    }
                }
                EOT
        ];

        yield 'It adds attribute to method implementing an interface method' => [
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                    public function foo(): void;
                }

                class SomeClass implements SomeInterface
                {
                    public function foo(): void
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                    public function foo(): void;
                }

                class SomeClass implements SomeInterface
                {
                    #[\Override]
                    public function foo(): void
                    {
                    }
                }
                EOT
        ];

        yield 'It adds attribute to method implementing a method of an interface implemented by an abstract parent' => [
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                    public function foo(): void;
                }

                abstract class AbstractClass implements SomeInterface
                {
                }

                class SomeClass extends AbstractClass
                {
                    public function foo(): void
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                    public function foo(): void;
                }

                abstract class AbstractClass implements SomeInterface
                {
                }

                class SomeClass extends AbstractClass
                {
                    #[\Override]
                    public function foo(): void
                    {
                    }
                }
                EOT
        ];

        yield 'It ignores method which already has the attribute' => [
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                    public function foo(): void;
                }

                class SomeClass implements SomeInterface
                {
                    #[\Override]
                    public function foo(): void
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                    public function foo(): void;
                }

                class SomeClass implements SomeInterface
                {
                    #[\Override]
                    public function foo(): void
                    {
                    }
                }
                EOT
        ];

        yield 'It ignores method with the unqualified attribute in an attribute group' => [
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                    public function foo(): void;
                }

                class SomeClass implements SomeInterface
                {
                    #[SomeAttribute, Override]
                    public function foo(): void
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                    public function foo(): void;
                }

                class SomeClass implements SomeInterface
                {
                    #[SomeAttribute, Override]
                    public function foo(): void
                    {
                    }
                }
                EOT
        ];

        yield 'It ignores method which does not override anything' => [
            <<<'EOT'
                <?php

                class ParentClass
                {
                    public function foo(): void
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    public function bar(): void
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class ParentClass
                {
                    public function foo(): void
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    public function bar(): void
                    {
                    }
                }
                EOT
        ];

        yield 'It ignores constructor of a parent class' => [
            <<<'EOT'
                <?php

                class ParentClass
                {
                    public function __construct()
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    public function __construct()
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class ParentClass
                {
                    public function __construct()
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    public function __construct()
                    {
                    }
                }
                EOT
        ];

        yield 'It adds attribute to constructor declared by an interface' => [
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                    public function __construct();
                }

                class SomeClass implements SomeInterface
                {
                    public function __construct()
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                    public function __construct();
                }

                class SomeClass implements SomeInterface
                {
                    #[\Override]
                    public function __construct()
                    {
                    }
                }
                EOT
        ];

        yield 'It ignores private parent method' => [
            <<<'EOT'
                <?php

                class ParentClass
                {
                    private function foo(): void
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    private function foo(): void
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class ParentClass
                {
                    private function foo(): void
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    private function foo(): void
                    {
                    }
                }
                EOT
        ];

        yield 'It adds attribute after a docblock' => [
            <<<'EOT'
                <?php

                class ParentClass
                {
                    public function foo(): void
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    /**
                     * This is a docblock.
                     */
                    public function foo(): void
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class ParentClass
                {
                    public function foo(): void
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    /**
                     * This is a docblock.
                     */
                    #[\Override]
                    public function foo(): void
                    {
                    }
                }
                EOT
        ];

        yield 'It adds attribute before other attributes' => [
            <<<'EOT'
                <?php

                class ParentClass
                {
                    public function foo(): void
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    #[SomeAttribute]
                    public function foo(): void
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class ParentClass
                {
                    public function foo(): void
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    #[\Override]
                    #[SomeAttribute]
                    public function foo(): void
                    {
                    }
                }
                EOT
        ];

        yield 'It adds attribute to abstract and static methods' => [
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                    public function foo(): void;

                    public static function bar(): void;
                }

                abstract class SomeClass implements SomeInterface
                {
                    abstract public function foo(): void;

                    public static function bar(): void
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                    public function foo(): void;

                    public static function bar(): void;
                }

                abstract class SomeClass implements SomeInterface
                {
                    #[\Override]
                    abstract public function foo(): void;

                    #[\Override]
                    public static function bar(): void
                    {
                    }
                }
                EOT
        ];

        yield 'It adds attribute to method overriding a parent method from another namespace' => [
            <<<'EOT'
                <?php

                namespace Foo {
                    class ParentClass
                    {
                        public function foo(): void
                        {
                        }
                    }
                }

                namespace Bar {
                    class ChildClass extends \Foo\ParentClass
                    {
                        public function foo(): void
                        {
                        }
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                namespace Foo {
                    class ParentClass
                    {
                        public function foo(): void
                        {
                        }
                    }
                }

                namespace Bar {
                    class ChildClass extends \Foo\ParentClass
                    {
                        #[\Override]
                        public function foo(): void
                        {
                        }
                    }
                }
                EOT
        ];

        yield 'It ignores trait methods' => [
            <<<'EOT'
                <?php

                class ParentClass
                {
                    public function foo(): void
                    {
                    }
                }

                trait SomeTrait
                {
                    public function foo(): void
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    use SomeTrait;
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class ParentClass
                {
                    public function foo(): void
                    {
                    }
                }

                trait SomeTrait
                {
                    public function foo(): void
                    {
                    }
                }

                class ChildClass extends ParentClass
                {
                    use SomeTrait;
                }
                EOT
        ];

        yield 'It adds attribute to method overriding a parent defined in another file' => [
            <<<'EOT'
                <?php

                class Foobar extends Bag
                {
                    public function bar(): Boo
                    {
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar extends Bag
                {
                    #[\Override]
                    public function bar(): Boo
                    {
                    }
                }
                EOT
        ];
    }

    public function testDoesNothingOnPhpVersionsBeforeOverrideWasIntroduced(): void
    {
        $example = <<<'EOT'
            <?php

            interface SomeInterface
            {
                public function foo(): void;
            }

            class SomeClass implements SomeInterface
            {
                public function foo(): void
                {
                }
            }
            EOT;

        $source = SourceCode::fromString($example);
        $transformer = new AddOverrideAttributeTransformer($this->reflectorForWorkspace($example), '8.2');
        $transformed = wait($transformer->transform($source));
        $this->assertEquals($example, (string) $transformed->apply($source));
        $this->assertCount(0, wait($transformer->diagnostics($source)));
    }

    public function testDiagnostics(): void
    {
        $example = <<<'EOT'
            <?php

            interface SomeInterface
            {
                public function foo(): void;
            }

            class SomeClass implements SomeInterface
            {
                public function foo(): void
                {
                }
            }
            EOT;

        $source = SourceCode::fromString($example);
        $transformer = new AddOverrideAttributeTransformer($this->reflectorForWorkspace($example), '8.3');
        $diagnostics = iterator_to_array(wait($transformer->diagnostics($source)));
        $this->assertCount(1, $diagnostics);
        $this->assertEquals(
            'Method "foo" overrides a parent method but has no #[\Override] attribute',
            reset($diagnostics)->message()
        );
    }
}
