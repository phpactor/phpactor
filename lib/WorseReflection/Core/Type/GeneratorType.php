<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Type;

class GeneratorType extends GenericClassType
{
    public function __construct(ClassReflector $reflector, Type $keyType, Type $valueType)
    {
        if ((!$keyType->isDefined() || $keyType instanceof ArrayKeyType) && $valueType->isDefined()) {
            parent::__construct($reflector, ClassName::fromString('Generator'), [ $valueType ]);
            return;
        }
        parent::__construct($reflector, ClassName::fromString('Generator'), [ $keyType, $valueType ]);
    }

    public function keyType(): Type
    {
        if (count($this->arguments) >= 2) {
            return $this->arguments[0];
        }

        return new MissingType();
    }

    public function valueType(): Type
    {
        if (count($this->arguments) === 1) {
            return $this->arguments[0];
        }
        if (count($this->arguments) >= 2) {
            return $this->arguments[1];
        }

        return new MissingType();
    }

    public function withValue(Type $type): GeneratorType
    {
        $new = clone $this;
        if (count($new->arguments) === 1) {
            $new->replaceArgument(0, $type);
            return $new;
        }
        if (count($this->arguments) === 2) {
            $new->replaceArgument(1, $type);
            return $new;
        }
        $new->arguments[] = $type;
        return $new;
    }

    public function withKey(Type $type): GeneratorType
    {
        $new = clone $this;
        if (count($this->arguments) === 2) {
            $new->replaceArgument(0, $type);
            return $new;
        }
        if (count($this->arguments) === 1) {
            $valueType = $this->arguments[0];
            $new->replaceArgument(0, $type);
            $new->arguments[] = $valueType;
            return $new;
        }
        return $new;
    }

    protected function map(Closure $mapper): Type
    {
        return new self(
            $this->reflector,
            $this->keyType()->map($mapper),
            $this->valueType()->map($mapper),
        );
    }
}
