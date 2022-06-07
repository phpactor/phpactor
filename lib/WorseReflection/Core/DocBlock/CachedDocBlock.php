<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Closure;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeResolver;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;

class CachedDocBlock implements DocBlock
{
    private DocBlock $docblock;

    private Cache $cache;

    private ?TypeResolver $typeResolver = null;


    public function __construct(DocBlock $docblock, Cache $cache)
    {
        $this->docblock = $docblock;
        $this->cache = $cache;
    }

    public function vars(): DocBlockVars
    {
        return $this->cached(__METHOD__, function () {
            return $this->docblock->vars();
        });
    }

    public function inherits(): bool
    {
        return $this->docblock->inherits();
    }

    public function deprecation(): Deprecation
    {
        return $this->cached(__METHOD__, function () {
            return $this->docblock->deprecation();
        });
    }

    public function templateMap(): TemplateMap
    {
        return $this->cached(__METHOD__, function () {
            return $this->docblock->templateMap();
        });
    }

    public function extends(): array
    {
        return $this->cached(__METHOD__, function () {
            return $this->docblock->extends();
        });
    }

    public function parameterType(string $paramName): Type
    {
        return $this->cached(__METHOD__.$paramName, function () use ($paramName) {
            return $this->docblock->parameterType($paramName);
        });
    }

    public function implements(): array
    {
        return $this->cached(__METHOD__, function () {
            return $this->docblock->implements();
        });
    }

    public function mixins(): array
    {
        return $this->cached(__METHOD__, function () {
            return $this->docblock->mixins();
        });
    }


    public function methodType(string $methodName): Type
    {
        return $this->cached(__METHOD__.$methodName, function () use ($methodName) {
            return $this->docblock->methodType($methodName);
        });
    }

    public function methods(ReflectionClassLike $declaringClass): ReflectionMethodCollection
    {
        return $this->cached(__METHOD__.$declaringClass->name()->__toString(), function () use ($declaringClass) {
            return $this->docblock->methods($declaringClass);
        });
    }

    public function properties(ReflectionClassLike $declaringClass): ReflectionPropertyCollection
    {
        return $this->cached(__METHOD__.$declaringClass->name()->__toString(), function () use ($declaringClass) {
            return $this->docblock->properties($declaringClass);
        });
    }

    public function isDefined(): bool
    {
        return $this->cached(__METHOD__, function () {
            return $this->docblock->isDefined();
        });
    }

    public function raw(): string
    {
        return $this->cached(__METHOD__, function () {
            return $this->docblock->raw();
        });
    }

    public function propertyType(string $methodName): Type
    {
        return $this->cached(__METHOD__.$methodName, function () use ($methodName) {
            return $this->propertyType($methodName);
        });
    }

    public function formatted(): string
    {
        return $this->cached(__METHOD__, function () {
            return $this->docblock->formatted();
        });
    }

    public function returnType(): Type
    {
        return $this->cached(__METHOD__, function () {
            return $this->docblock->returnType();
        });
    }

    public function withTypeResolver(TypeResolver $typeResolver): DocBlock
    {
        $this->typeResolver = $typeResolver;
        return new self($this->docblock->withTypeResolver($typeResolver), $this->cache);
    }

    /**
     * @template T
     * @param Closure(): T $closure
     * @return T
     */
    private function cached(string $key, Closure $closure)
    {
        if ($this->typeResolver) {
            $key = get_class($this->typeResolver);
        }
        return $this->cache->getOrSet($key, $closure);
    }
}
