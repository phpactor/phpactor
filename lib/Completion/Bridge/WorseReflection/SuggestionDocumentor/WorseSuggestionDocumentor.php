<?php

namespace Phpactor\Completion\Bridge\WorseReflection\SuggestionDocumentor;

use Closure;
use Phpactor\Completion\Bridge\ObjectRenderer\ItemDocumentation;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Core\SuggestionDocumentor;
use Phpactor\ObjectRenderer\Model\ObjectRenderer;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Reflector;

class WorseSuggestionDocumentor implements SuggestionDocumentor
{
    private Reflector $reflector;
    private ObjectRenderer $renderer;

    public function __construct(Reflector $reflector, ObjectRenderer $renderer)
    {
        $this->reflector = $reflector;
        $this->renderer = $renderer;
    }

    public function document(Suggestion $suggestion): Closure
    {
        return function () use ($suggestion) {
            if ($suggestion->type() === Suggestion::TYPE_CLASS) {
                try {
                    $reflectionClass = $this->reflector->reflectClassLike($suggestion->fqn());
                } catch (NotFound $notFound) {
                    return $suggestion->documentation();
                }

                return $this->renderer->render(new ItemDocumentation(
                    $reflectionClass->name(),
                    $reflectionClass->docblock()->formatted(),
                    $reflectionClass
                ));
            }

            return $suggestion->documentation();
        };
    }
}
