<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\CatchClause;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\DelimitedList\ListExpressionList;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Statement\ForeachStatement;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Expression\ListIntrinsicExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Phpactor\WorseReflection\Core\Inference\Resolver\BinaryExpressionResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ForeachStatementResolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\AggregateType;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\ArrayShapeType;
use Phpactor\WorseReflection\Core\Type\Literal;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\TypeUtil;
use Psr\Log\LoggerInterface;

class PassThroughWalker extends AbstractWalker
{
    public function nodeFqns(): array
    {
        return [
            ReturnStatement::class,
            IfStatementNode::class,
            ForeachStatement::class,
            CatchClause::class,
            BinaryExpression::class,
            CallExpression::class,
            AssignmentExpression::class
        ];
    }

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        assert($node instanceof AssignmentExpression);

        $resolver->resolveNode($frame, $node);

        return $frame;
    }
}
