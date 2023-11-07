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
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;

/**
 * Report if trying to call a class method which does not exist.
 */
class MissingMemberProvider implements DiagnosticProvider
{
    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if ((!$node instanceof CallExpression)) {
            return;
        }

        $memberName = null;
        if ($node->callableExpression instanceof MemberAccessExpression) {
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

        $methodName = $memberName->getText($node->getFileContents());
        if (!is_string($methodName)) {
            return;
        }
        try {
            $name = $containerType->members()->byMemberType(ReflectionMember::TYPE_METHOD)->get($methodName);
        } catch (NotFound) {
            yield new MissingMethodDiagnostic(
                ByteOffsetRange::fromInts(
                    $memberName->getStartPosition(),
                    $memberName->getEndPosition()
                ),
                sprintf(
                    'Method "%s" does not exist on class "%s"',
                    $methodName,
                    $containerType->__toString()
                ),
                DiagnosticSeverity::ERROR(),
                $containerType->name()->__toString(),
                $methodName
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
    }

    public function name(): string
    {
        return 'missing_method';
    }
}
