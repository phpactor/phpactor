<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use PHPUnit\Framework\Assert;
use Phpactor\WorseReflection\Core\DiagnosticExample;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Inference\Context\FunctionCallContext;
use Phpactor\WorseReflection\Core\Inference\Context\MemberAccessContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\SuperGlobals;
use Phpactor\WorseReflection\Core\Inference\Variable as PhpactorVariable;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

/**
 * Report if a variable is undefined and suggest variables with similar names.
 */
class UndefinedVariableProvider implements DiagnosticProvider
{
    public function __construct(private int $suggestionLevensteinDistance = 4)
    {
    }

    public function examples(): iterable
    {
        yield new DiagnosticExample(
            title: 'undefined variable',
            source: <<<'PHP'
                <?php

                $zebra = 'one';
                $foa = 'two';

                if ($foo) {
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Undefined variable "$foo", did you mean "$foa"', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'property',
            source: <<<'PHP'
                <?php

                class Foo {
                    public $foo;
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'many undefined variables',
            source: <<<'PHP'
                <?php

                $foz = 'one';
                $foa = 'two';
                $fob = 'three';

                if ($foo) {
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Undefined variable "$foo", did you mean one of "$foz", "$foa", "$fob"', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'from vardoc',
            source: <<<'PHP'
                <?php

                /** @var string $zebra */
                $zebra = 'one';

                if ($zebra) {
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'was assigned',
            source: <<<'PHP'
                <?php

                class Foo {
                    public function foo(): void
                    {
                        $this->bar;
                    }

                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'is catch receiver',
            source: <<<'PHP'
                <?php

                try {
                    foo();
                } catch (Error $e) {
                    throw $e;
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'unnamed var!',
            source: <<<'PHP'
                <?php

                class foo {
                    public string $
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'variable is parameter',
            source: <<<'PHP'
                <?php

                function ($foo) {
                    return $foo;
                }

                class Foo {
                    public function foo($foo) {
                        return $foo;
                    }
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'is this',
            source: <<<'PHP'
                <?php

                $zebra = 'one';
                $foa = 'two';

                if ($foa) {
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'is in enum',
            source: <<<'PHP'
                <?php

                enum Foo: string {
                    public function foo(): void
                    {
                        $this;
                    }
                }
                PHP,
            valid: true,
            minPhpVersion: '8.1',
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'is in list assignment',
            source: <<<'PHP'
                <?php

                [$a, $b] = [1, 2];

                echo $a;
                echo $b;
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'undefined and no suggestions',
            source: <<<'PHP'
                <?php

                if ($foa) {
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Undefined variable "$foa"', $diagnostics->at(0)->message());
            }
        );

        yield new DiagnosticExample(
            title: 'after for loop',
            source: <<<'PHP'
                <?php

                $plainArray = [];
                $list = [];
                foreach ($plainArray as $index => $data) {
                    $list[$index] = $data;
                }

                return $list;
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );

        yield new DiagnosticExample(
            title: 'static',
            source: <<<'PHP'
                <?php
                class Foo
                {
                    public static string $foo = 'bar';

                    public function demo(): void
                    {
                        $example = static::$foo;
                    }
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'pass by reference',
            source: <<<'PHP'
                <?php
                function preg_match(string $pattern, string $string, array &$matches): bool {}
                preg_match('foobar', 'barfoo', $matches);
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'pass by reference introduces var',
            source: <<<'PHP'
                <?php
                function preg_match(string $pattern, string $string, array &$matches): bool {}
                preg_match('foobar', 'barfoo', $matches);
                echo $matches[1];
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'super globals',
            source: <<<'PHP'
                <?php
                $GLOBALS['foo'];
                $_SERVER['foo'];
                $_GET['foo'];
                $_POST['foo'];
                $_FILES['foo'];
                $_COOKIE['foo'];
                $_SESSION['foo'];
                $_REQUEST['foo'];
                $_ENV['foo'];

                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'local globals',
            source: <<<'PHP'
                <?php
                function foo(): void
                {
                    global $foo, $bar;

                    echo $foo;
                    echo $bar;
                }

                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'local static',
            source: <<<'PHP'
                <?php
                function foo(): void
                {
                    static $foo, $bar;

                    echo $foo;
                    echo $bar;
                }

                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'dynamic pass by ref',
            source: <<<'PHP'
                <?php
                function foo(?int &$v1, ?string &$v2): void
                {
                    $v1 = 123;
                    $v2 = '456';
                }

                foo($var1, $var2);
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'dynamic pass by ref (method)',
            source: <<<'PHP'
                <?php
                class Foo {
                    public function foo(?int &$v1, ?string &$v2): void
                    {
                        $v1 = 123;
                        $v2 = '456';
                    }
                }

                $foo = new Foo();
                $foo->foo($var1, $var2);
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'multiple @var declarations followed by binary expression in statement',
            source: <<<'PHP'
                <?php
                /** @var string $a */
                /** @var string $b */
                /** @var string $c */
                echo $a . $b;
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'multiple @var declarations',
            source: <<<'PHP'
                <?php
                /** @var string $a */
                /** @var string $b */
                /** @var string $c */
                echo $a;
                echo $b;
                echo $c;
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
    }

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }

    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof Variable) {
            return [];
        }
        if ($node->parent?->parent instanceof PropertyDeclaration) {
            return [];
        }
        if ($node->parent instanceof ScopedPropertyAccessExpression) {
            return [];
        }

        if (!$name = $node->getName()) {
            return [];
        }

        $global = SuperGlobals::list()[$name] ?? null;

        if ($global) {
            return [];
        }

        foreach ($frame->locals()->byName($name) as $variable) {
            if ($variable->wasDefinition()) {
                return [];
            }
        }

        yield new UndefinedVariableDiagnostic(
            NodeUtil::byteOffsetRangeForNode($node),
            $name,
            array_filter(array_map(function (PhpactorVariable $var) {
                return $var->name();
            }, $frame->locals()->definitionsOnly()->mostRecent()->toArray()), function (string $candidate) use ($name) {
                return levenshtein($name, $candidate) < $this->suggestionLevensteinDistance;
            })
        );
    }

    public function name(): string
    {
        return 'undefined_variable';
    }
}
