<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use InvalidArgumentException;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode as PrototypeSourceCode;
use RuntimeException;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeTransform\Domain\Refactor\GenerateAccessor;

class WorseGenerateAccessor implements GenerateAccessor
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var Updater
     */
    private $updater;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var bool
     */
    private $upperCaseFirst;

    public function __construct(
        Reflector $reflector,
        Updater $updater,
        string $prefix = '',
        bool $upperCaseFirst = null
    ) {
        $this->reflector = $reflector;
        $this->updater = $updater;
        $this->prefix = $prefix;
        $this->upperCaseFirst = ($prefix && $upperCaseFirst === null) || $upperCaseFirst;
    }

    public function generate(SourceCode $sourceCode, string $propertyName, int $offset): SourceCode
    {
        $property = $this->class((string) $sourceCode, $offset)
             ->properties()
             ->get($propertyName);

        $prototype = $this->buildPrototype($property);
        $sourceCode = $this->sourceFromClassName($sourceCode, $property->class()->name());

        return $sourceCode->withSource((string) $this->updater->textEditsFor(
            $prototype,
            Code::fromString((string) $sourceCode)
        )->apply(Code::fromString((string) $sourceCode)));
    }

    private function formatName(string $name): string
    {
        if ($this->upperCaseFirst) {
            $name = ucfirst($name);
        }

        return $this->prefix . $name;
    }

    private function buildPrototype(ReflectionProperty $property): PrototypeSourceCode
    {
        $builder = SourceCodeBuilder::create();
        $className = $property->class()->name();

        $builder->namespace($className->namespace());
        $method = $builder
            ->class($className->short())
            ->method($this->formatName($property->name()));
        $method->body()->line(sprintf('return $this->%s;', $property->name()));

        $type = $property->inferredTypes()->best();
        if ($type->isDefined()) {
            $className = $type->className();
            $method->returnType($className ? $className->short() : $type->primitive());
        }

        return $builder->build();
    }

    private function sourceFromClassName(SourceCode $sourceCode, ClassName $className): SourceCode
    {
        $containingClass = $this->reflector->reflectClassLike($className);
        $worseSourceCode = $containingClass->sourceCode();

        if ($worseSourceCode->path() != $sourceCode->path()) {
            return $sourceCode;
        }

        return SourceCode::fromStringAndPath(
            $worseSourceCode->__toString(),
            $worseSourceCode->path()
        );
    }

    private function class(string $source, int $offset): ReflectionClass
    {
        $classes = $this->reflector->reflectClassesIn($source);

        if (0 === $classes->count()) {
            throw new InvalidArgumentException(
                'No classes in source file'
            );
        }

        if (1 === $classes->count()) {
            return $classes->first();
        }

        foreach ($this->reflector->reflectClassesIn($source) as $class) {
            $position = $class->position();

            if ($position->start() <= $offset && $offset <= $position->end()) {
                return $class;
            }
        }

        throw new RuntimeException('Impossible to determine which class to use.');
    }
}
