<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

trait ComparableTrait
{
    public function identical(Type $right): BooleanType
    {
        if (!$this instanceof Literal) {
            return TypeFactory::bool();
        }

        if ($right instanceof ScalarType && $right instanceof Literal) {
            return TypeFactory::boolLiteral($this->value() === $right->value());
        }

        return TypeFactory::bool();
    }

    public function greaterThan(Type $right): BooleanType
    {
    }

    public function greaterThanEqual(Type $right): BooleanType
    {
    }

    public function compare(Type $right): BooleanType
    {
    }

    public function lessThan(Type $right): BooleanType
    {
    }

    public function notEqual(Type $right): BooleanType
    {
    }

    public function lessThanEqual(Type $right): BooleanType
    {
    }

    public function equal(Type $right): BooleanType
    {
        if ($right instanceof ScalarType && $right instanceof Literal) {
            return TypeFactory::boolLiteral($this->value() == $right->value());
        }

        return TypeFactory::bool();
    }

    public function notIdentical(Type $type): BooleanType
    {
    }

}
