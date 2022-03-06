<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use LogicException;
use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\Helper\VariableCompletionHelper;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Reflector;

abstract class AbstractParameterCompletor
{
    /**
     * @var Reflector
     */
    protected $reflector;

    /**
     * @var VariableCompletionHelper
     */
    protected $variableCompletionHelper;

    /**
     * @var ObjectFormatter
     */
    private $formatter;

    public function __construct(Reflector $reflector, ObjectFormatter $formatter, VariableCompletionHelper $variableCompletionHelper = null)
    {
        $this->reflector = $reflector;
        $this->formatter = $formatter;
        $this->variableCompletionHelper = $variableCompletionHelper ?: new VariableCompletionHelper($reflector);
    }

    protected function populateResponse(Node $callableExpression, ReflectionFunctionLike $functionLikeReflection, array $variables): Generator
    {
        // function has no parameters, return empty handed
        if ($functionLikeReflection->parameters()->count() === 0) {
            return true;
        }

        $paramIndex = $this->paramIndex($callableExpression);

        if ($this->numberOfArgumentsExceedParameterArity($functionLikeReflection, $paramIndex)) {
            return true;
        }

        $parameter = $this->reflectedParameter($functionLikeReflection, $paramIndex);

        foreach ($variables as $variable) {
            if (
                $variable->symbolContext()->types()->count() &&
                false === $this->isVariableValidForParameter($variable, $parameter)
            ) {
                // parameter has no types and is not valid for this position, ignore it
                continue;
            }

            yield Suggestion::createWithOptions(
                '$' . $variable->name(),
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'priority' => Suggestion::PRIORITY_HIGH,
                    'short_description' => sprintf(
                        '%s => param #%d %s',
                        $this->formatter->format($variable->symbolContext()->types()),
                        $paramIndex,
                        $this->formatter->format($parameter)
                    )
                ]
            );
        }

        return true;
    }

    private function paramIndex(Node $node): int
    {
        $argumentList = $this->argumentListFromNode($node);

        if (null === $argumentList) {
            return 1;
        }

        $index = 0;
        /** @var ArgumentExpression $element */
        foreach ($argumentList->getElements() as $element) {
            $index++;
            if (!$element->expression instanceof Variable) {
                continue;
            }

            $name = $element->expression->getName();

            if ($name instanceof MissingToken) {
                continue;
            }
        }

        // if we have a trailing comma, e.g. the argument list is `$foobar, `
        // then the above elements will contain only `$foobar` but the param
        // index should be incremented.
        if (substr(trim($argumentList->getText()), -1, 1) === ',') {
            return $index + 1;
        }

        return $index;
    }

    private function isVariableValidForParameter(WorseVariable $variable, ReflectionParameter $parameter): bool
    {
        if ($parameter->inferredTypes()->best() == Type::undefined()) {
            return true;
        }

        /** @var Type $variableType */
        foreach ($variable->symbolContext()->types() as $variableType) {
            $variableTypeClass = null;
            if ($variableType->isClass()) {
                try {
                    $variableTypeClass = $this->reflector->reflectClassLike($variableType->className());
                } catch (NotFound $e) {
                    return false;
                }
            }

            foreach ($parameter->inferredTypes() as $parameterType) {
                if ($variableType == $parameterType) {
                    return true;
                }

                if ($variableTypeClass && $parameterType->isClass() && $variableTypeClass->isInstanceOf($parameterType->className())) {
                    return true;
                }
            }
        }
        return false;
    }

    private function reflectedParameter(ReflectionFunctionLike $reflectionFunctionLike, int $paramIndex): ReflectionParameter
    {
        $reflectedIndex = 1;
        /** @var ReflectionParameter $parameter */
        foreach ($reflectionFunctionLike->parameters() as $parameter) {
            if ($reflectedIndex == $paramIndex) {
                return $parameter;
            }
            $reflectedIndex++;
        }

        throw new LogicException(sprintf('Could not find parameter for index "%s"', $paramIndex));
    }

    private function numberOfArgumentsExceedParameterArity(ReflectionFunctionLike $reflectionFunctionLike, int $paramIndex): bool
    {
        return $reflectionFunctionLike->parameters()->count() < $paramIndex;
    }

    /**
     * @return ArgumentExpressionList|null
     */
    private function argumentListFromNode(Node $node)
    {
        if ($node instanceof ObjectCreationExpression) {
            return $node->argumentExpressionList;
        }

        if ($node instanceof QualifiedName) {
            $callExpression = $node->parent;
            assert($callExpression instanceof CallExpression);
            return $callExpression->argumentExpressionList;
        }
        
        assert($node instanceof MemberAccessExpression || $node instanceof ScopedPropertyAccessExpression);
        assert(null !== $node->parent);

        $list = $node->parent->getFirstDescendantNode(ArgumentExpressionList::class);
        assert($list instanceof ArgumentExpressionList || is_null($list));

        return $list;
    }
}
