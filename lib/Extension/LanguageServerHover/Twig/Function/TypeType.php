<?php

namespace Phpactor\Extension\LanguageServerHover\Twig\Function;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;

class TypeType
{
    public function __invoke(Type $type): ?string
    {
        if ($type instanceof ReflectedClassType) {
            return $this->typeFromReflected($type);
        }

        return null;
    }

    private function typeFromReflected(ReflectedClassType $type): ?string
    {
        $reflection = $type->reflectionOrNull();

        if (null === $reflection) {
            return null;
        }

        if ($reflection instanceof ReflectionInterface) {
            return 'interface';
        }

        if ($reflection instanceof ReflectionClass) {
            return 'class';
        }

        if ($reflection instanceof ReflectionTrait) {
            return 'trait';
        }

        if ($reflection instanceof ReflectionEnum) {
            return 'enum';
        }

        return '';
    }
}
