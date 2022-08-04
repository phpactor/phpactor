<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\ObjectRenderer\ItemDocumentation;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\ObjectRenderer\Model\ObjectRenderer;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Reflector;

class WorseDocumentingCompletor implements TolerantCompletor
{
    private TolerantCompletor $innerCompletor;
    private Reflector $reflector;
    private ObjectRenderer $objectRenderer;

    public function __construct(
        TolerantCompletor $innerCompletor,
        Reflector $reflector,
        ObjectRenderer $objectRenderer
    )
    {
        $this->innerCompletor = $innerCompletor;
        $this->reflector = $reflector;
        $this->objectRenderer = $objectRenderer;
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        foreach ($this->innerCompletor->complete($node, $source, $offset) as $suggestion) {
            if ($suggestion->type() === Suggestion::TYPE_CLASS) {
                $suggestion = $suggestion->withDocumentation(function () use ($suggestion) {
                    try {
                        $classLike = $this->reflector->reflectClassLike($suggestion->name());
                    } catch (NotFound $e) {
                        return $suggestion->documentation();
                    }

                    return $this->objectRenderer->render(new ItemDocumentation(
                        $suggestion->label(),
                        $classLike->docblock()->formatted(),
                        $classLike
                    ));
                });
            }
        }
    }
}
