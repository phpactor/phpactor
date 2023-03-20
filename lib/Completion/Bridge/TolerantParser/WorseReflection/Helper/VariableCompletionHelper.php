<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection\Helper;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\Variable as ParserVariable;
use Phpactor\Completion\Bridge\TolerantParser\CompletionContext;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Reflector;

class VariableCompletionHelper
{
    public function __construct(protected Reflector $reflector)
    {
    }

    /**
     * @return Variable[]
     */
    public function variableCompletions(Node $node, TextDocument $source, ByteOffset $offset): array
    {
        $partialMatch = '';
        if ($node instanceof ParserVariable) {
            $partialMatch = $node->getText();
        }

        $offset = $this->offsetToReflect($node, $offset->toInt());

        try {
            $reflectionOffset = $this->reflector->reflectOffset($source, $offset);
        } catch (NotFound) {
            return [];
        }

        $frame = $reflectionOffset->frame();

        if (CompletionContext::anonymousUse($node)) {
            $frame = $frame->parent();
        }

        if (null === $frame) {
            return [];
        }

        // Get all declared variables up until the start of the current
        // expression. The most recently declared variables should be first
        // (which is why we reverse the array).
        $reversedLocals = $this->orderedVariablesUntilOffset($frame, $node->getStartPosition());

        // Ignore variables that have already been suggested.
        $seen = [];
        $variables = [];

        /** @var Variable $local */
        foreach ($reversedLocals as $local) {
            if (isset($seen[$local->name()])) {
                continue;
            }


            $name = ltrim($partialMatch, '$');
            $matchPos = -1;

            if ($name) {
                $matchPos = mb_strpos($local->name(), $name);
            }

            // if there is a partial match and the variable does not start with
            // it, skip the variable.
            if ($partialMatch && ('$' !== $partialMatch && 0 !== $matchPos)) {
                continue;
            }

            $seen[$local->name()] = true;
            $variables[] = $local;
        }

        return $variables;
    }

    private function offsetToReflect(Node $node, int $offset): int
    {
        $parentNode = $node->parent;

        // If the parent is an assignment expression, then only parse
        // until the start of the expression, not the start of the variable
        // under completion:
        //
        //     $left = $lef<>
        //
        // Otherwise $left will be evaluated to <unknown>.
        if ($parentNode instanceof AssignmentExpression) {
            $offset = $parentNode->getFullStartPosition();
        }

        return $offset;
    }

    private function orderedVariablesUntilOffset(Frame $frame, int $offset): array
    {
        return array_reverse(iterator_to_array($frame->locals()->lessThan($offset)));
    }
}
