<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\CatchClause;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Expression\YieldExpression;
use Microsoft\PhpParser\Node\Statement\ForeachStatement;
use Microsoft\PhpParser\Node\Statement\GlobalDeclaration;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\StaticVariableDeclaration;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Phpactor\WorseReflection\Core\Inference\Walker;

/**
 * Temporary class to bridge to the node resolvers (originally all these
 * classes were "walkers") the goal is to remove the "walker" concept.
 */
class PassThroughWalker implements Walker
{
    public function nodeFqns(): array
    {
        return [
            YieldExpression::class,
            ReturnStatement::class,
            IfStatementNode::class,
            GlobalDeclaration::class,
            StaticVariableDeclaration::class,
            ForeachStatement::class,
            CatchClause::class,
            BinaryExpression::class,
            CallExpression::class,
            AssignmentExpression::class,
            Variable::class,
        ];
    }

    public function enter(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        $resolver->resolveNode($frame, $node);

        return $frame;
    }

    public function exit(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        return $frame;
    }
}
