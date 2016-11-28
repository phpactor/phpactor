<?php

namespace Phpactor\Complete\Provider;

use PhpParser\Node;
use Phpactor\Complete\CompleteContext;
use PhpParser\Node\Stmt;

class VariableProvider
{
    public function canProvide($tokens)
    {
        return count($tokens === 1) && 1 === strpos($tokens[0], '$');
    }

    public function provide(CompleteContext $context)
    {
        $method = $this->getContainerNode($context, Stmt\ClassMethod::class);
        $class = $this->getContainerNode($context, Stmt\Class_::class);
        $namespace = $this->getContainerNode($context, Stmt\Namespace_::class);
        var_dump($namespace);die();;
    }

    private function getLocalVars(array $stmts, int $lineNb)
    {
    }

    private function getContainerNode(CompleteContext $context, string $type = null, $best = null)
    {
        foreach ($context->getStmts() as $stmt) {
            $best = $this->doGetContainerNode($stmt, $context->getLineNb(), $type, $best);
        }

        return $best;
    }

    private function doGetContainerNode($node, $lineNb, $type, $best = null)
    {
        if ($best === null) {
            $best = $node;
        }

        if ($node->getAttribute('startLine') > $lineNb) {
            return $best;
        }

        // if the line is contained in this node
        if ($lineNb >= $node->getAttribute('startLine') && $lineNb <= $node->getAttribute('endLine')) {
            // if the node is better than the best ...
            if (($type === null || get_class($node) === $type) && $node->getAttribute('startLine') > $best->getAttribute('startLine')) {
                $best = $node;
            }
        }

        // skip non-structural nodes
        if (isset($node->stmts)) {
            foreach ($node->stmts as $stmt) {
                $best = $this->doGetContainerNode($stmt, $lineNb, $type, $best);
            }
        }

        return $best;
    }
}
