<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\ClassHierarchyResolver;
use Phpactor\WorseReflection\Core\Inference\GenericMapResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\TypeUtil;

class ParameterTypeResolver
{
    public function __construct(private ReflectionParameter $parameter, private GenericMapResolver $mapResolver)
    {
    }

    public function resolve(): Type
    {
        $functionLike = $this->parameter->functionLike();

        $type = $this->resolveType($functionLike, $this->parameter);

        return $type;
    }

    public function resolveType(ReflectionFunctionLike $functionLike, ReflectionParameter $parameter): Type
    {
        if (!$functionLike instanceof ReflectionMethod) {
            $docblock = $functionLike->docblock();
            $docblockType = $docblock->parameterType($parameter->name());
            return TypeUtil::firstDefined($docblockType, $parameter->type());
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
            $type = $docblock->parameterType($parameter->name());

            if (!$type->isDefined()) {
                continue;
            }

            return $this->resolveGenericType($functionLike->class(), $classLike, $type);
        }

        return $parameter->type();
    }

    private function resolveGenericType(
        ReflectionClassLike $topClass,
        ReflectionClassLike $bottomClass,
        Type $type
    ): Type {
        $topTemplateMap = $topClass->docblock()->templateMap();
        if ($topTemplateMap->has($type->__toString())) {
            return $type;
        }
        $map = $this->mapResolver->resolveClassTemplateMap($topClass->type(), $bottomClass->name(), []);
        if (!$map) {
            return $type;
        }
        if ($map->has($type->short())) {
            $t = $map->get($type->short());
            if (!$t->isDefined()) {
                return $type;
            }
            return $t;
        }
        return $type;
    }
}
