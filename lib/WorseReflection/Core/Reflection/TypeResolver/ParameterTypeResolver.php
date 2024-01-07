<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\ClassHierarchyResolver;
use Phpactor\WorseReflection\Core\Inference\GenericMapResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\TypeUtil;

class ParameterTypeResolver
{
    public function __construct(private ReflectionParameter $parameter, private GenericMapResolver $mapResolver)
    {
    }

    public function resolve(): Type
    {
        $functionLike = $this->parameter->functionLike();

        $type = $this->resolveType($functionLike);

        return $type;
    }

    public function resolveType(ReflectionFunctionLike $functionLike): Type
    {
        if (!$functionLike instanceof ReflectionMethod) {
            $docblock = $this->parameter->functionLike()->docblock();
            $docblockType = $docblock->parameterType($this->parameter->name());
            return TypeUtil::firstDefined($docblockType, $this->parameter->type());
        }

        $hierarchy = (new ClassHierarchyResolver(
            ClassHierarchyResolver::INCLUDE_PARENT | ClassHierarchyResolver::INCLUDE_INTERFACE
        ))->resolve($functionLike->class());

        foreach ($hierarchy as $classLike) {
            // find declaring class
            if (!$classLike->methods()->has($functionLike->name())) {
                continue;
            }
            $docblock = $classLike->methods()->get($functionLike->name())->docblock();
            $type = $docblock->parameterType($this->parameter->name());
            if (!$type->isDefined()) {
                continue;
            }

            return $this->resolveGenericType($functionLike->class(), $classLike, $type);
        }

        return $this->parameter->type();
    }

    private function resolveGenericType(
        ReflectionClassLike $topClass,
        ReflectionClassLike $bottomClass,
        Type $type
    ): Type
    {
        $topTemplateMap = $topClass->docblock()->templateMap();
        if ($topTemplateMap->has($type->__toString())) {
            return $type;
        }
        $map = $this->mapResolver->resolveClassTemplateMap($topClass->type(), $bottomClass->name(), []);
        if (!$map) {
            return $type;
        }
        if ($map->has($type->__toString())) {
            $t = $map->get($type->__toString());
            if (!$t->isDefined()) {
                return $type;
            }
            return $t;
        }
        return $type;
    }
}
