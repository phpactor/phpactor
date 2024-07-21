<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

class ConditionalType extends Type
{
    public function __construct(private string $variable, private Type $isType, private Type $left, private Type $right)
    {
    }

    public function __toString(): string
    {
        return sprintf(
            '%s is %s ? %s : %s',
            $this->variable,
            $this->isType->__toString(),
            $this->left->__toString(),
            $this->right->__toString()
        );
    }

    public function toPhpString(): string
    {
        return 'mixed';
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::maybe();
    }

    public function evaluate(ReflectionFunctionLike $functionLike, FunctionArguments $functionArguments): Type
    {
        try {
            $parameter = $functionLike->parameters()->get(ltrim($this->variable, '$'));
        } catch (NotFound) {
            return TypeFactory::undefined();
        }

        $argumentType = $this->resolveArgumentType($functionArguments, $parameter);

        $evaluator = function (Type $type) use ($functionLike, $functionArguments): Type {
            if ($type instanceof ParenthesizedType && $type->type instanceof ConditionalType) {
                return $type->type->evaluate($functionLike, $functionArguments);
            }
            return $type;
        };

        if (!$argumentType->isDefined()) {
            return $evaluator($this->right);
        }

        if ($this->isType->accepts($argumentType)->isTrue()) {
            return $evaluator($this->left);
        }

        return $evaluator($this->right);
    }

    public function map(Closure $mapper): Type
    {
        return new ConditionalType(
            $this->variable,
            $this->isType->map($mapper),
            $this->left->map($mapper),
            $this->right->map($mapper)
        );
    }

    private function resolveArgumentType(
        FunctionArguments $functionArguments,
        ReflectionParameter $parameter
    ): Type {
        if ($functionArguments->has($parameter->index())) {
            return $functionArguments->at($parameter->index())->type();
        }
        if ($parameter->default()->isDefined()) {
            return TypeFactory::fromValue($parameter->default()->value());
        }
        return TypeFactory::mixed();
    }
}
