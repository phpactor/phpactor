<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use PHPUnit\Framework\Assert;
use Phpactor\WorseReflection\Core\DiagnosticExample;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Inference\Context\ClassLikeContext;
use Phpactor\WorseReflection\Core\Inference\Context\FunctionCallContext;
use Phpactor\WorseReflection\Core\Inference\Context\MemberAccessContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;

/**
 * Report when a deprecated symbol (class, method, constant, function etc) is used.
 */
class DeprecatedUsageDiagnosticProvider implements DiagnosticProvider
{
    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof MemberAccessExpression && !$node instanceof ScopedPropertyAccessExpression && !$node instanceof QualifiedName) {
            return;
        }

        $resolved = $resolver->resolveNode($frame, $node);

        if ($resolved instanceof MemberAccessContext) {
            yield from $this->memberAccessDiagnostics($resolved);
        }
        if ($resolved instanceof ClassLikeContext) {
            yield from $this->classLikeDiagnostics($resolved);
        }
        if ($resolved instanceof FunctionCallContext) {
            yield from $this->functionDiagnostics($resolved);
        }
    }

    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }

    public function examples(): iterable
    {
        yield new DiagnosticExample(
            title: 'deprecated class',
            source: <<<'PHP'
                <?php

                /** @deprecated */
                class Deprecated {
                    public static foo(): void {}
                }

                class NotDeprecated {
                    public static foo(): void {}
                }

                $fo = new Deprecated();
                Deprecated::foo();
                new NotDeprecated();
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(2, $diagnostics);
                Assert::assertEquals('Call to deprecated class "Deprecated"', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'deprecated constant',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    /** @deprecated This is deprecated */
                    const FOO = 'BAR';

                    const BAR = 'BAR';

                    public function foo(Closure $foobar) {
                        $fo = self::FOO;
                        $ba = self::BAR;
                    }
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Call to deprecated constant "FOO": This is deprecated', $diagnostics->at(0)->message());
            }
        );

        yield new DiagnosticExample(
            title: 'deprecated enum',
            source: <<<'PHP'
                <?php

                /** @deprecated */
                enum Deprecated {
                    case FOO;
                }

                enum NotDeprecated {
                    case BAR;
                }

                $fo = Deprecated::FOO();
                Deprecated::foo();
                new NotDeprecated();
                PHP,
            valid: false,
            minPhpVersion: '8.1',
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(2, $diagnostics);
                Assert::assertEquals('Call to deprecated enum "Deprecated"', $diagnostics->at(0)->message());
            }
        );

        yield new DiagnosticExample(
            title: 'deprecated function',
            source: <<<'PHP'
                <?php

                /** @deprecated */
                function bar(): void {}

                function notDeprecated(): void {}

                bar();

                notDeprecated();
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Call to deprecated function "bar"', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'deprecated method',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    public function foo(Closure $foobar) {
                        $this->deprecated();
                        $this->notDeprecated();
                    }

                    /** @deprecated This is deprecated */
                    public function deprecated(): void {}

                    public function notDeprecated(): void {}
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Call to deprecated method "deprecated": This is deprecated', $diagnostics->at(0)->message());
            }
        );

        yield new DiagnosticExample(
            title: 'deprecated on trait',
            source: <<<'PHP'
                <?php

                trait FoobarTrait {
                    /** @deprecated This is deprecated */
                    public function deprecated(): void {}
                }

                class Foobar
                {
                    use FoobarTrait;
                    public function foo(Closure $foobar) {
                        $this->deprecated();
                        $this->notDeprecated();
                    }

                    public function notDeprecated(): void {}
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Call to deprecated method "deprecated": This is deprecated', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'deprecated on property',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    /** @deprecated This is deprecated */
                    public string $deprecated;

                    public string $notDeprecated;

                    public function foo(Closure $foobar) {
                        $fo = $this->deprecated;
                        $ba = $this->notDeprecated;
                    }
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Call to deprecated property "deprecated": This is deprecated', $diagnostics->at(0)->message());
            }
        );
    }

    /**
     * @param MemberAccessContext<ReflectionMember> $resolved
     * @return Generator<DeprecatedUsageDiagnostic>
     */
    private function memberAccessDiagnostics(MemberAccessContext $resolved): Generator
    {
        $member = $resolved->accessedMember();
        if (!$member->deprecation()->isDefined()) {
            return;
        }

        yield new DeprecatedUsageDiagnostic(
            $resolved->memberNameRange(),
            $member->name(),
            $member->deprecation()->message(),
            $member->memberType(),
        );
    }
    /**
     * @return Generator<DeprecatedUsageDiagnostic>
     */
    private function classLikeDiagnostics(ClassLikeContext $resolved): Generator
    {
        $reflectionClass = $resolved->classLike();
        if (!$reflectionClass->deprecation()->isDefined()) {
            return;
        }

        yield new DeprecatedUsageDiagnostic(
            $resolved->range(),
            $reflectionClass->name(),
            $reflectionClass->deprecation()->message(),
            $reflectionClass->classLikeType(),
        );
    }
    /**
     * @return Generator<DeprecatedUsageDiagnostic>
     */
    private function functionDiagnostics(FunctionCallContext $resolved): Generator
    {
        $reflectionFunction = $resolved->function();
        if (!$reflectionFunction->docblock()->deprecation()->isDefined()) {
            return;
        }

        yield new DeprecatedUsageDiagnostic(
            $resolved->range(),
            $reflectionFunction->name(),
            $reflectionFunction->docblock()->deprecation()->message(),
            'function',
        );
    }
}
