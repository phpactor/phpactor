<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\ClassNotFound;
use Phpactor\WorseReflection\Core\Exception\FunctionNotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionFunctionCollection;

class CoreReflector implements ClassReflector, SourceCodeReflector, FunctionReflector
{
    private SourceCodeReflector $sourceReflector;
    
    private SourceCodeLocator $sourceLocator;

    public function __construct(SourceCodeReflector $sourceReflector, SourceCodeLocator $sourceLocator)
    {
        $this->sourceReflector = $sourceReflector;
        $this->sourceLocator = $sourceLocator;
    }

    /**
     * Reflect class.
     *
     * @throws ClassNotFound If the class was not found, or the class found was
     *         an interface or trait.
     */
    public function reflectClass($className): ReflectionClass
    {
        $className = ClassName::fromUnknown($className);

        $class = $this->reflectClassLike($className);

        if (false === $class instanceof ReflectionClass) {
            throw new ClassNotFound(sprintf(
                '"%s" is not a class, it is a "%s"',
                $className->full(),
                get_class($class)
            ));
        }

        return $class;
    }

    /**
     * Reflect an interface.
     *
     * @param ClassName|string $className
     *
     * @throws ClassNotFound If the class was not found, or the found class
     *         was not a trait.
     */
    public function reflectInterface($className): ReflectionInterface
    {
        $className = ClassName::fromUnknown($className);

        $class = $this->reflectClassLike($className);

        if (false === $class instanceof ReflectionInterface) {
            throw new ClassNotFound(sprintf(
                '"%s" is not an interface, it is a "%s"',
                $className->full(),
                get_class($class)
            ));
        }

        return $class;
    }

    /**
     * Reflect a trait
     *
     * @param ClassName|string $className
     *
     * @throws ClassNotFound If the class was not found, or the found class
     *         was not a trait.
     */
    public function reflectTrait($className): ReflectionTrait
    {
        $className = ClassName::fromUnknown($className);

        $class = $this->reflectClassLike($className);

        if (false === $class instanceof ReflectionTrait) {
            throw new ClassNotFound(sprintf(
                '"%s" is not a trait, it is a "%s"',
                $className->full(),
                get_class($class)
            ));
        }

        return $class;
    }
    
    public function reflectEnum($className): ReflectionEnum
    {
        $className = ClassName::fromUnknown($className);

        $class = $this->reflectClassLike($className);

        if (false === $class instanceof ReflectionEnum) {
            throw new ClassNotFound(sprintf(
                '"%s" is not an enum, it is a "%s"',
                $className->full(),
                get_class($class)
            ));
        }

        return $class;
    }

    /**
     * Reflect a class, trait or interface by its name.
     *
     * If the class it not found an exception will be thrown.
     *
     * @throws ClassNotFound
     */
    public function reflectClassLike($className): ReflectionClassLike
    {
        $className = ClassName::fromUnknown($className);

        $source = $this->sourceLocator->locate($className);
        $classes = $this->reflectClassesIn($source);

        if (false === $classes->has((string) $className)) {
            throw new ClassNotFound(sprintf(
                'Unable to locate class "%s"',
                $className->full()
            ));
        }

        $class = $classes->get((string) $className);

        return $class;
    }

    /**
     * Reflect all classes (or class-likes) in the given source code.
     */
    public function reflectClassesIn($sourceCode): ReflectionClassCollection
    {
        return $this->sourceReflector->reflectClassesIn($sourceCode);
    }

    /**
     * Return the information for the given offset in the given file, including the value
     * and type of a variable and the frame information.
     *
     * @param SourceCode|string $sourceCode
     * @param Offset|int $offset
     */
    public function reflectOffset($sourceCode, $offset): ReflectionOffset
    {
        return $this->sourceReflector->reflectOffset($sourceCode, $offset);
    }

    public function reflectMethodCall($sourceCode, $offset): ReflectionMethodCall
    {
        return $this->sourceReflector->reflectMethodCall($sourceCode, $offset);
    }
    
    public function reflectFunctionsIn($sourceCode): ReflectionFunctionCollection
    {
        return $this->sourceReflector->reflectFunctionsIn($sourceCode);
    }

    public function reflectFunction($name): ReflectionFunction
    {
        $name = Name::fromUnknown($name);

        $source = $this->sourceLocator->locate($name);
        $functions = $this->reflectFunctionsIn($source);

        if (false === $functions->has((string) $name)) {
            throw new FunctionNotFound(sprintf(
                'Unable to locate function "%s"',
                $name->full()
            ));
        }

        $function = $functions->get((string) $name);

        return $function;
    }
    
    public function sourceCodeForFunction($name): SourceCode
    {
        return $this->sourceLocator->locate(Name::fromUnknown($name));
    }
    
    public function sourceCodeForClassLike($name): SourceCode
    {
        return $this->sourceLocator->locate(Name::fromUnknown($name));
    }
}
