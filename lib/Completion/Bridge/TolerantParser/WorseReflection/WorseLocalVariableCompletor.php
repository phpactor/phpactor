<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\Helper\VariableCompletionHelper;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Reflector;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Microsoft\PhpParser\Node\Expression\Variable as TolerantVariable;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;

class WorseLocalVariableCompletor implements TolerantCompletor
{
    /**
     * @var ObjectFormatter
     */
    private $informationFormatter;

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var VariableCompletionHelper
     */
    private $variableCompletionHelper;


    public function __construct(Reflector $reflector, ObjectFormatter $typeFormatter = null, VariableCompletionHelper $variableCompletionHelper = null)
    {
        $this->reflector = $reflector;
        $this->informationFormatter = $typeFormatter ?: new ObjectFormatter();
        $this->variableCompletionHelper = $variableCompletionHelper ?: new VariableCompletionHelper($reflector);
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (false === $this->couldComplete($node, $source, $offset)) {
            return true;
        }

        foreach ($this->variableCompletionHelper->variableCompletions($node, $source, $offset) as $local) {
            yield Suggestion::createWithOptions(
                '$' . $local->name(),
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'short_description' => $this->informationFormatter->format($local)
                ]
            );
        }

        return true;
    }

    private function couldComplete(Node $node = null, TextDocument $source, ByteOffset $offset): bool
    {
        if (null === $node) {
            return false;
        }

        $parentNode = $node->parent;

        if ($parentNode instanceof MemberAccessExpression) {
            return false;
        }

        if ($parentNode instanceof ScopedPropertyAccessExpression) {
            return false;
        }

        if ($node instanceof TolerantVariable) {
            return true;
        }

        return false;
    }
}
