<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use PHPUnit\Framework\Assert;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\DiagnosticExample;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

/**
 * Report when assigning to a missing property definition.
 */
class AssignmentToMissingPropertyProvider implements DiagnosticProvider
{
    public function examples(): iterable
    {
        yield new DiagnosticExample(
            title: 'to non-existing property',
            source: <<<'PHP'
                <?php

                class Foobar {
                    public function baz(){ 
                        $this->bar = 'foo';
                    }
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals(
                    'Property "bar" has not been defined',
                    $diagnostics->at(0)->message()
                );
            }
        );
        yield new DiagnosticExample(
            title: 'does not report assignment for existing property',
            source: <<<'PHP'
                <?php

                class Foobar {
                    private string $bar;
                    public function baz(){ 
                        $this->bar = 'foo';
                    }
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
    }

    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof AssignmentExpression) {
            return;
        }

        $memberAccess = $node->leftOperand;
        $accessExpression = null;
        if ($memberAccess instanceof SubscriptExpression) {
            /** @phpstan-ignore-next-line Access expression is NULL if list addition */
            $accessExpression = $memberAccess->accessExpression ?: $memberAccess;
            $memberAccess = $memberAccess->postfixExpression;
        }

        if (!$memberAccess instanceof MemberAccessExpression) {
            return;
        }

        $deref = $memberAccess->dereferencableExpression;

        if (!$deref instanceof Variable) {
            return;
        }

        if ($deref->getText() !== '$this') {
            return;
        }

        $memberNameToken = $memberAccess->memberName;

        if (!$memberNameToken instanceof Token) {
            return;
        }

        $memberName = $memberNameToken->getText($node->getFileContents());

        if (!is_string($memberName)) {
            return;
        }

        $rightOperand = $node->rightOperand;

        if (!$rightOperand instanceof Expression) {
            return;
        }

        $classNode = NodeUtil::nodeContainerClassLikeDeclaration($node);

        if (null === $classNode) {
            return;
        }

        try {
            $class = $resolver->reflector()->reflectClassLike($classNode->getNamespacedName()->__toString());
        } catch (NotFound) {
            return;
        }

        if (!$class instanceof ReflectionTrait && !$class instanceof ReflectionClass) {
            return;
        }

        if ($class->properties()->has($memberName)) {
            return;
        }

        yield new AssignmentToMissingPropertyDiagnostic(
            ByteOffsetRange::fromInts(
                $node->getStartPosition(),
                $node->getEndPosition()
            ),
            $class->name()->__toString(),
            $memberName,
            $this->resolvePropertyType($resolver, $frame, $rightOperand, $accessExpression),
            $accessExpression ? true : false,
        );
    }

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }

    public function name(): string
    {
        return 'assignment_to_missing_property';
    }

    private function resolvePropertyType(
        NodeContextResolver $resolver,
        Frame $frame,
        Expression $rightOperand,
        Node|MissingToken|null $accessExpression
    ): Type {
        $type = $resolver->resolveNode($frame, $rightOperand)->type();

        if (!$accessExpression instanceof Node) {
            return $type;
        }

        return new ArrayType(
            $accessExpression instanceof SubscriptExpression ? null : $resolver->resolveNode($frame, $accessExpression)->type(),
            $type
        );
    }
}
