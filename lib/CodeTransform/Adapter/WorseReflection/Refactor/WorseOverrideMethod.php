<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode as PhpactorSourceCode;
use Phpactor\CodeTransform\Domain\Refactor\OverrideMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Type\ClassType;
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
    public function __construct(
        private Reflector $reflector,
        private BuilderFactory $factory,
        private Updater $updater
    ) {
    }

    public function overrideMethod(SourceCode $source, string $className, string $methodName): TextEdits
    {
        $class = $this->getReflectionClass($source, $className);
        $method = $this->getAncestorReflectionMethod($class, $methodName);

        $methodBuilder = $this->getMethodPrototype($class, $method);
        $sourcePrototype = $this->getSourcePrototype($class, $method, $source, $methodBuilder);

        return $this->updater->textEditsFor($sourcePrototype, Code::fromString((string) $source));
    }

    private function getReflectionClass(SourceCode $source, string $className): ReflectionClass
    {
        $builder = TextDocumentBuilder::create($source)->language('php');
        if ($source->uri()->path()) {
            $builder->uri($source->uri());
        }

        $classes = $this->reflector->reflectClassesIn($builder->build())->classes();

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

        foreach ($method->returnType()->allTypes()->classLike() as $classType) {
            $usedClasses[] = $classType;
        }

        /**
         * @var ReflectionParameter $parameter */
        foreach ($method->parameters() as $parameter) {
            foreach ($parameter->type()->expandTypes()->classLike() as $classType) {
                $usedClasses[] = $classType;
            }
        }

        foreach ($usedClasses as $usedClass) {
            assert($usedClass instanceof ClassType);
            $className = $usedClass->name();

            if ($class->name()->namespace() == $className->namespace()) {
                continue;
            }

            $sourceBuilder->use((string) $usedClass);
        }
    }
}
