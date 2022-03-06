<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode as PhpactorSourceCode;
use Phpactor\CodeTransform\Domain\Refactor\OverrideMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeBuilder\Domain\BuilderFactory;

class WorseOverrideMethod implements OverrideMethod
{
    /**
     * @var Updater
     */
    private $updater;

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var BuilderFactory
     */
    private $factory;

    public function __construct(Reflector $reflector, BuilderFactory $factory, Updater $updater)
    {
        $this->factory = $factory;
        $this->updater = $updater;
        $this->reflector = $reflector;
    }

    public function overrideMethod(SourceCode $source, string $className, string $methodName): string
    {
        $class = $this->getReflectionClass($source, $className);
        $method = $this->getAncestorReflectionMethod($class, $methodName);

        $methodBuilder = $this->getMethodPrototype($class, $method);
        $sourcePrototype = $this->getSourcePrototype($class, $method, $source, $methodBuilder);

        return $this->updater->textEditsFor($sourcePrototype, Code::fromString((string) $source))->apply($source);
    }

    private function getReflectionClass(SourceCode $source, string $className): ReflectionClass
    {
        $builder = TextDocumentBuilder::create($source)->language('php');
        if ($source->path()) {
            $builder->uri($source->path());
        }

        $classes = $this->reflector->reflectClassesIn($builder->build());

        return $classes->get($className);
    }

    private function getMethodPrototype(ReflectionClass $class, ReflectionMethod $method): MethodBuilder
    {
        /** @var ReflectionMethod $method */
        $builder = $this->factory->fromSource(
            $method->class()->sourceCode()
        );

        $methodBuilder = $builder->class($method->declaringClass()->name()->short())->method($method->name());

        return $methodBuilder;
    }

    private function getAncestorReflectionMethod(ReflectionClass $class, string $methodName): ReflectionMethod
    {
        if (null === $class->parent()) {
            throw new TransformException(sprintf(
                'Class "%s" has no parent, cannot override any method',
                $class->name()
            ));
        }

        return $class->parent()->methods()->get($methodName);
    }

    private function getSourcePrototype(ReflectionClass $class, ReflectionMethod $method, SourceCode $source, MethodBuilder $methodBuilder): PhpactorSourceCode
    {
        $sourceBuilder = $this->factory->fromSource($source);
        $sourceBuilder->class($class->name()->short())->add($methodBuilder);
        $this->importClasses($class, $method, $sourceBuilder);

        return $sourceBuilder->build();
    }

    private function importClasses(ReflectionClass $class, ReflectionMethod $method, SourceCodeBuilder $sourceBuilder): void
    {
        $usedClasses = [];

        if ($method->returnType()->isDefined() && $method->returnType()->isClass()) {
            $usedClasses[] = $method->returnType();
        }

        /**
         * @var ReflectionParameter $parameter */
        foreach ($method->parameters() as $parameter) {
            if (false === $parameter->type()->isDefined() || false === $parameter->type()->isClass()) {
                continue;
            }

            $usedClasses[] = $parameter->type();
        }

        foreach ($usedClasses as $usedClass) {
            $className = $usedClass->className();

            if (!$className) {
                continue;
            }

            if ($class->name()->namespace() == $className->namespace()) {
                continue;
            }

            $sourceBuilder->use((string) $usedClass);
        }
    }
}
