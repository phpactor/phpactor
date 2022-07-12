<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class ConditionalType extends Type
{
    private Type $isType;
    private Type $left;
    private Type $right;

    private string $variable;

    public function __construct(
        string $variable,
        Type $isType,
        Type $left,
        Type $right
    ) {
        $this->isType = $isType;
        $this->left = $left;
        $this->right = $right;
        $this->variable = $variable;
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
        $parameter = $functionLike->parameters()->get(ltrim($this->variable, '$'));
        $argumentType = $functionArguments->at($parameter->index())->type();
        if (!$argumentType->isDefined()) {
            return $this->right;
        }

        if ($this->isType->accepts($argumentType)->isTrue()) {
            return $this->left;
        }

        return $this->right;
    }
}
