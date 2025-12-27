<?php

namespace Phpactor\WorseReflection\Core\Reflector\ClassReflector;

use Closure;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\Exception\CycleDetected;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionDeclaredConstant;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflector\ConstantReflector;
use Phpactor\WorseReflection\Core\Reflector\FunctionReflector;
use Phpactor\TextDocument\TextDocument;

class MemonizedReflector implements ClassReflector, FunctionReflector, ConstantReflector
{
    private const FUNC_PREFIX = '__func__';
    private const CLASS_PREFIX = '__class__';
    private const INTERFACE_PREFIX = '__interface__';
    private const TRAIT_PREFIX = '__trait__';
    private const ENUM_PREFIX = '__enum__';
    private const CLASS_LIKE_PREFIX = '__class_like__';

    private readonly ClassReflector $innerReflector;

    public function __construct(
        private readonly ClassReflector $classReflector,
        private readonly FunctionReflector $functionReflector,
        private readonly ConstantReflector $constantReflector,
        private readonly Cache $cache
    ) {
        $this->innerReflector = $classReflector;
    }

    public function reflectClass($className): ReflectionClass
    {
        return $this->getOrSet(self::CLASS_PREFIX.$className, function () use ($className) {
            return $this->classReflector->reflectClass($className);
        });
    }

    public function reflectInterface($className, array $visited = []): ReflectionInterface
    {
        return $this->getOrSet(self::INTERFACE_PREFIX.$className, function () use ($className, $visited) {
            return $this->classReflector->reflectInterface($className, $visited);
        });
    }

    public function reflectTrait($className, array $visited = []): ReflectionTrait
    {
        return $this->getOrSet(self::TRAIT_PREFIX.$className, function () use ($className, $visited) {
            return $this->classReflector->reflectTrait($className, $visited);
        });
    }

    public function reflectEnum($className): ReflectionEnum
    {
        return $this->getOrSet(self::ENUM_PREFIX.$className, function () use ($className) {
            return $this->classReflector->reflectEnum($className);
        });
    }

    public function reflectClassLike($className, $visited = []): ReflectionClassLike
    {
        if (isset($visited[(string)$className])) {
            throw new CycleDetected(sprintf(
                'Cycle detected while resolving class "%s"',
                (string)$className
            ));
        }
        return $this->getOrSet(self::CLASS_LIKE_PREFIX.(string)$className, function () use ($className, $visited) {
            return $this->classReflector->reflectClassLike($className, $visited);
        });
    }

    public function reflectFunction($name): ReflectionFunction
    {
        return $this->getOrSet(self::FUNC_PREFIX.$name, function () use ($name) {
            return $this->functionReflector->reflectFunction($name);
        });
    }

    public function sourceCodeForFunction($name): TextDocument
    {
        return $this->getOrSet(self::FUNC_PREFIX.'source_code'.$name, function () use ($name): TextDocument {
            return $this->functionReflector->sourceCodeForFunction($name);
        });
    }

    public function sourceCodeForClassLike($name): TextDocument
    {
        return $this->getOrSet(self::CLASS_LIKE_PREFIX.'source_code'.$name, function () use ($name) {
            return $this->classReflector->sourceCodeForClassLike($name);
        });
    }

    public function reflectConstant($name): ReflectionDeclaredConstant
    {
        return $this->constantReflector->reflectConstant($name);
    }

    public function sourceCodeForConstant($name): TextDocument
    {
        return $this->constantReflector->sourceCodeForConstant($name);
    }

    /**
     * @template T
     * @param Closure(): T $closure
     * @return T
     */
    private function getOrSet(string $key, Closure $closure)
    {
        $closure = function () use ($closure) {
            try {
                return $closure();
            } catch (NotFound $e) {
                return $e;
            }
        };
        $result = $this->cache->getOrSet($key, $closure);
        if ($result instanceof NotFound) {
            throw $result;
        }
        return $result;
    }
}
