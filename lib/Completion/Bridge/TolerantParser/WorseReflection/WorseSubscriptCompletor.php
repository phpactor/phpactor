<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\Type\ArrayShapeType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class WorseSubscriptCompletor implements TolerantCompletor
{
    public function __construct(private SourceCodeReflector $reflector)
    {
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (false === $this->couldComplete($node, $source, $offset)) {
            return true;
        }

        $offset = $this->reflector->reflectOffset($source, $node->getEndPosition());
        $type = $offset->nodeContext()->type();

        if (!$type instanceof ArrayShapeType) {
            return true;
        }

        foreach ($type->keys() as $key) {
            yield Suggestion::createWithOptions(sprintf('[\'%s\']', (string)$key), [
                'type' => Suggestion::TYPE_FIELD,
                'short_description' => $type->typeAtOffset($key)->__toString(),
            ]);
        }

        return true;
    }

    private function couldComplete(Node $node = null, TextDocument $source, ByteOffset $offset): bool
    {
        return $node instanceof SubscriptExpression;
    }
}
