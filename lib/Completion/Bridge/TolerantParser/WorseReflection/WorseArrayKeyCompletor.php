<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Microsoft\PhpParser\ResolvedName;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\ClassQualifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifiable;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;
use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Type\ArrayShapeType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

class WorseArrayKeyCompletor implements TolerantCompletor
{
    private Reflector $reflector;

    private ObjectFormatter $formatter;


    public function __construct(Reflector $reflector, ObjectFormatter $formatter)
    {
        $this->reflector = $reflector;
        $this->formatter = $formatter;
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (!$node instanceof SubscriptExpression) {
            return true;
        }

        $offset = $this->reflector->reflectOffset($source->__toString(), $node->getStartPosition());
        $type = $offset->symbolContext()->type();
        if ($type instanceof ArrayShapeType) {
            foreach ($type->typeMap as $key => $type) {
                if (is_numeric($key)) {
                    yield Suggestion::createWithOptions((string)$key, [
                        'type' => Suggestion::TYPE_FIELD,
                        'short_description' => $this->formatter->format($type),
                    ]);
                    continue;
                }

                yield Suggestion::createWithOptions('\'' . $key . '\'', [
                    'type' => Suggestion::TYPE_FIELD,
                    'short_description' => $this->formatter->format($type)
                ]);
            }
        }

        return true;
    }
}

