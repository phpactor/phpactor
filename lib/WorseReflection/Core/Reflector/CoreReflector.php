<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Generator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionNavigation;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Exception\ClassNotFound;
use Phpactor\WorseReflection\Core\Exception\ConstantNotFound;
use Phpactor\WorseReflection\Core\Exception\CycleDetected;
use Phpactor\WorseReflection\Core\Exception\FunctionNotFound;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionDeclaredConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionDeclaredConstant;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassLikeCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionFunctionCollection;

class CoreReflector implements ClassReflector, SourceCodeReflector, FunctionReflector, ConstantReflector
{
    public function __construct(private SourceCodeReflector $sourceReflector, private SourceCodeLocator $sourceLocator)
    {
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
    public function reflectInterface($className, array $visited = []): ReflectionInterface
    {
        $className = ClassName::fromUnknown($className);

        $class = $this->reflectClassLike($className, $visited);

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
    public function reflectTrait($className, array $visited = []): ReflectionTrait
    {
        $className = ClassName::fromUnknown($className);

        $class = $this->reflectClassLike($className, $visited);

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
    public function reflectClassLike($className, array $visited = []): ReflectionClassLike
    {
        $className = ClassName::fromUnknown($className);

        if (isset($visited[$className->__toString()])) {
            throw new CycleDetected(sprintf(
                'Cycle detected while resolving class "%s"',
                $className->full()
            ));
        }
        $visited[$className->__toString()] = true;

        $source = $this->sourceLocator->locate($className);
        $classes = $this->reflectClassesIn($source, $visited);

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
    public function reflectClassesIn($sourceCode, array $visited = []): ReflectionClassLikeCollection
    {
        return $this->sourceReflector->reflectClassesIn($sourceCode, $visited);
    }

    /**
     * Return the information for the given offset in the given file, including the value
     * and type of a variable and the frame information.
     *
     * @param SourceCode|string $sourceCode
     * @param ByteOffset|int $offset
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

    public function reflectConstantsIn($source): ReflectionDeclaredConstantCollection
    {
        return $this->sourceReflector->reflectConstantsIn($source);
    }

    public function navigate($sourceCode): ReflectionNavigation
    {
        return $this->sourceReflector->navigate($sourceCode);
    }

    /**
     * @param Name|string $name
     */
    public function reflectFunction($name): ReflectionFunction
    {
        $name = Name::fromUnknown($name);

        // if the source is not found, fallback to the global
        // function
        try {
            $source = $this->sourceLocator->locate($name);
        } catch (NotFound) {
            $name = Name::fromString($name->short());
            $source = $this->sourceLocator->locate($name);
        }

        $functions = $this->reflectFunctionsIn($source);

        if (false === $functions->has((string) $name)) {
            $name = Name::fromString($name->short());
            $source = $this->sourceLocator->locate($name);
            $functions = $this->reflectFunctionsIn($source);
            if (false === $functions->has($name)) {
                throw new FunctionNotFound(sprintf(
                    'Unable to locate function "%s"',
                    $name
                ));
            }
        }

        $function = $functions->get((string) $name);

        return $function;
    }

    public function sourceCodeForFunction($name): TextDocument
    {
        return $this->sourceLocator->locate(Name::fromUnknown($name));
    }

    public function sourceCodeForClassLike($name): TextDocument
    {
        return $this->sourceLocator->locate(Name::fromUnknown($name));
    }

    public function diagnostics($sourceCode): Diagnostics
    {
        return $this->sourceReflector->diagnostics($sourceCode);
    }

    public function reflectNode($sourceCode, $offset): ReflectionNode
    {
        return $this->sourceReflector->reflectNode($sourceCode, $offset);
    }

    public function walk(TextDocument $sourceCode, Walker $walker): Generator
    {
        return $this->sourceReflector->walk($sourceCode, $walker);
    }

    public function reflectConstant($name): ReflectionDeclaredConstant
    {
        $name = Name::fromUnknown($name);

        // if the source is not found, fallback to the global
        // function
        try {
            $source = $this->sourceLocator->locate($name);
        } catch (NotFound) {
            $name = Name::fromString($name->short());
            $source = $this->sourceLocator->locate($name);
        }

        $constants = $this->reflectConstantsIn($source);

        if (false === $constants->has((string) $name)) {
            $name = Name::fromString($name->short());
            $source = $this->sourceLocator->locate($name);
            $constants = $this->reflectConstantsIn($source);
            if (false === $constants->has($name)) {
                throw new ConstantNotFound(sprintf(
                    'Unable to locate constant "%s"',
                    $name
                ));
            }
        }

        return $constants->get((string) $name);
    }

    public function sourceCodeForConstant($name): TextDocument
    {
        $name = Name::fromUnknown($name);

        // if the source is not found, fallback to the global
        // function
        try {
            $source = $this->sourceLocator->locate($name);
        } catch (NotFound) {
            $name = Name::fromString($name->short());
            $source = $this->sourceLocator->locate($name);
        }

        return $source;
    }
}
