<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Helper;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\Literal;
use Phpactor\WorseReflection\Core\Type\NullType;
use Phpactor\WorseReflection\Core\Type\NullableType;
use Phpactor\WorseReflection\Core\Type\StringLiteralType;

class EmptyValueRenderer
{
    public function render(Type $type): string
    {
        if ($type instanceof NullType || $type instanceof NullableType) {
            return 'null';
        }

        if ($type instanceof ClassType) {
            return sprintf('new %s()', $type->name()->head()->__toString());
        }

        if (!$type instanceof Literal) {
            return sprintf('/** %s */', $type->__toString());
        }

        if ($type instanceof StringLiteralType) {
            return sprintf('\'%s\'', $type->value());
        }

        if ($type instanceof ArrayLiteral) {
            return sprintf('[%s]', implode(', ', array_map(fn (Type $value) => $this->render($value), $type->iterableValueTypes())));
        }

        return $type->__toString();
    }
}
