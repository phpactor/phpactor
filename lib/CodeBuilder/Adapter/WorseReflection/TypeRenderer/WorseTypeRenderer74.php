<?php

namespace Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\AggregateType;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\InvokeableType;
use Phpactor\WorseReflection\Core\Type\NullableType;
use Phpactor\WorseReflection\Core\Type\PseudoIterableType;
use Phpactor\WorseReflection\Core\Type\ScalarType;
use Phpactor\WorseReflection\Core\Type\SelfType;
use Phpactor\WorseReflection\Core\Type\VoidType;

class WorseTypeRenderer74 implements WorseTypeRenderer
{
    public function render(Type $type): ?string
    {
        if ($type instanceof NullableType) {
            return '?' . $this->render($type->type);
        }

        if ($type instanceof AggregateType) {
            return null;
        }

        if ($type instanceof ArrayType) {
            return $type->toPhpString();
        }

        if ($type instanceof BooleanType) {
            return 'bool';
        }

        if ($type instanceof ScalarType) {
            return $type->toPhpString();
        }

        if ($type instanceof GenericClassType) {
            return $type->name()->short();
        }

        if ($type instanceof ClassType) {
            return $type->short();
        }

        if ($type instanceof SelfType) {
            return $type->__toString();
        }

        if ($type instanceof VoidType) {
            return $type->__toString();
        }

        if ($type instanceof InvokeableType) {
            return $type->toPhpString();
        }

        if ($type instanceof PseudoIterableType) {
            return $type->toPhpString();
        }

        return null;
    }
}
