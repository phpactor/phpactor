<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Token;
use PHPUnit\Framework\Assert;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\DiagnosticExample;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;

/**
 * Report if trying to call a class method which does not exist.
 */
class MissingMemberProvider implements DiagnosticProvider
{
    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (
            !$node instanceof CallExpression &&
            !($node instanceof ScopedPropertyAccessExpression && !$node->parent instanceof CallExpression)
        ) {
            return;
        }

        $memberName = null;
        $memberType = null;
        if ($node instanceof ScopedPropertyAccessExpression) {
            $memberName = $node->memberName;
        } elseif ($node->callableExpression instanceof MemberAccessExpression) {
            $memberName = $node->callableExpression->memberName;
        } elseif ($node->callableExpression instanceof ScopedPropertyAccessExpression) {
            $memberName = $node->callableExpression->memberName;
        }

        if (!($memberName instanceof Token)) {
            return;
        }

        $containerType = $resolver->resolveNode($frame, $node)->containerType();

        if (!$containerType->isDefined()) {
            return;
        }

        if (!$containerType instanceof ReflectedClassType) {
            return;
        }

        $reflection = $containerType->reflectionOrNull();
        if (null === $reflection) {
            return;
        }

        $methodName = $memberName->getText($node->getFileContents());
        if (!is_string($methodName)) {
            return;
        }

        $memberTypes = (function (ReflectionClassLike $reflection) use ($node) {
            if ($node instanceof ScopedPropertyAccessExpression) {
                $types = [ReflectionMember::TYPE_CONSTANT];

                if ($reflection instanceof ReflectionEnum) {
                    $types[] = ReflectionMember::TYPE_CASE;
                }

                return $types;
            }
            return [ReflectionMember::TYPE_METHOD];
        })($reflection);


        $found = false;
        foreach ($memberTypes as $memberType) {
            try {
                $containerType->members()->byMemberType($memberType)->get($methodName);
            } catch (NotFound) {
                continue;
            }
            $found = true;
        }

        if (!$found) {
            yield new MissingMemberDiagnostic(
                ByteOffsetRange::fromInts(
                    $memberName->getStartPosition(),
                    $memberName->getEndPosition()
                ),
                sprintf(
                    '%s "%s" does not exist on %s "%s"',
                    ucfirst($memberType),
                    $methodName,
                    $reflection->classLikeType(),
                    $containerType->__toString()
                ),
                DiagnosticSeverity::ERROR(),
                $containerType->name()->__toString(),
                $methodName,
                $memberType,
            );
        }
    }

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }

    public function examples(): iterable
    {
        yield new DiagnosticExample(
            title: 'inlined type',
            source: <<<'PHP'
                <?php

                class Type {
                }

                class ReflectedClassType extends Type {
                    public function isInvokable(): bool {
                        return true;
                    }
                }

                function (Type $type) {
                    if ($type instanceof ReflectedClassType && $type->isInvokable()) {
                    }
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'does not report call on type inferred previously in expressio',
            source: <<<'PHP'
                <?php

                class Type {
                }

                class ReflectedClassType extends Type {
                    public function isInvokable(): bool {
                        return true;
                    }
                }

                function (Type $type) {
                    if ($type instanceof ReflectedClassType && $type->isInvokable()) {
                    }
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'missing method on instance ',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                }

                $f = new Foobar();
                $f->bar();
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Method "bar" does not exist on class "Foobar"', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'missing method for static invocation',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                }

                Foobar::bar();
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Method "bar" does not exist on class "Foobar"', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'missing enum case',
            source: <<<'PHP'
                <?php

                enum Foobar
                {
                    case Foo;
                }

                Foobar::Foo;
                Foobar::Bar;
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Case "Bar" does not exist on enum "Foobar"', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'enum contains const and case',
            source: <<<'PHP'
                <?php

                enum Foobar
                {
                    case Foo;
                    public const Bar = 'Bar';
                }

                Foobar::Foo;
                Foobar::Bar;
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'enum static method not existing',
            source: <<<'PHP'
                <?php

                enum Foobar
                {
                }

                Foobar::foobar();
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'enum cases',
            source: <<<'PHP'
                <?php

                enum Foobar
                {
                }

                Foobar::cases();
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'missing constant on class',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    const FOO = 'bar';
                }

                Foobar::FOO;
                Foobar::BAR;
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Constant "BAR" does not exist on class "Foobar"', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'missing property on class is not supported yet',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    public int $foo;
                }

                $f = new Foobar();
                $f->foo = 12;
                $f->barfoo = 'string';
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
    }

    public function name(): string
    {
        return 'missing_method';
    }
}
