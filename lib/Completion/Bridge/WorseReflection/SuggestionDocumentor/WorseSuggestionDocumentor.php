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
            $fqn = $suggestion->fqn();

            if (null === $fqn) {
                return '';
            }

            if ($suggestion->type() === Suggestion::TYPE_CLASS) {
                try {
                    $reflectionClass = $this->reflector->reflectClassLike($fqn);
                } catch (NotFound $notFound) {
                    return $suggestion->documentation();
                }

                return $this->renderer->render(new ItemDocumentation(
                    $reflectionClass->name(),
                    $reflectionClass->docblock()->formatted(),
                    $reflectionClass
                ));
            }

            if ($suggestion->type() === Suggestion::TYPE_FUNCTION) {
                try {
                    $reflectionFunction = $this->reflector->reflectFunction($fqn);
                } catch (NotFound $notFound) {
                    return $suggestion->documentation();
                }

                return $this->renderer->render(new ItemDocumentation(
                    $reflectionFunction->name(),
                    $reflectionFunction->docblock()->formatted(),
                    $reflectionFunction
                ));
            }

            if ($suggestion->type() === Suggestion::TYPE_CONSTANT) {
                try {
                    $reflectionConstant = $this->reflector->reflectConstant($fqn);
                } catch (NotFound $notFound) {
                    return $suggestion->documentation();
                }

                return $this->renderer->render(new ItemDocumentation(
                    $reflectionConstant->name(),
                    $reflectionConstant->docblock()->formatted(),
                    $reflectionConstant
                ));
            }

            return $suggestion->documentation();
        };
    }
}
