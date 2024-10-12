<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\Helper\VariableCompletionHelper;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Type\ArrayShapeType;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Microsoft\PhpParser\Node\Expression\Variable as TolerantVariable;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;

class WorseLocalVariableCompletor implements TolerantCompletor
{
    private ObjectFormatter $informationFormatter;

    public function __construct(
        private VariableCompletionHelper $variableCompletionHelper,
        ?ObjectFormatter $typeFormatter = null
    ) {
        $this->informationFormatter = $typeFormatter ?: new ObjectFormatter();
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (false === $this->couldComplete($node, $source, $offset)) {
            return true;
        }

        foreach ($this->variableCompletionHelper->variableCompletions($node, $source, $offset) as $local) {
            $localType = $local->type();
            if ($localType instanceof ArrayShapeType) {
                yield from $this->arrayShapeSuggestions($local->name(), $localType);
            }

            yield Suggestion::createWithOptions(
                '$' . $local->name(),
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'short_description' => $this->informationFormatter->format($local),
                    'documentation' => function () use ($local) {
                        return $local->type()->__toString();
                    },
                ]
            );
        }

        return true;
    }

    private function couldComplete(?Node $node, TextDocument $source, ByteOffset $offset): bool
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

    /**
     * @return Generator<Suggestion>
     */
    private function arrayShapeSuggestions(string $varName, ArrayShapeType $localType): Generator
    {
        foreach ($localType->typeMap as $key => $type) {
            $key = is_numeric($key) ? $key : '\'' . $key . '\'';
            yield 'why'.$key => Suggestion::createWithOptions(sprintf('$%s[%s]', $varName, (string)$key), [
                'type' => Suggestion::TYPE_FIELD,
                'short_description' => $this->informationFormatter->format($type),
                'documentation' => function () use ($type) {
                    return $type->__toString();
                },
            ]);
        }
    }
}
