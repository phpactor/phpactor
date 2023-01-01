<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeTransform\Domain\Refactor\GenerateDecorator;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextEdits;
use Phpactor\CodeBuilder\Domain\Builder\MethodBuilder;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Reflector;

class WorseGenerateDecorator implements GenerateDecorator
{
    public function __construct(private Reflector $reflector, private Updater $updater)
    {
    }

    public function getTextEdits(SourceCode $source, string $interfaceFQN): TextEdits
    {
        $class = $this->reflector->reflectClassesIn($source)->classes()->first();

        $builder = SourceCodeBuilder::create();
        $builder->namespace($class->name()->namespace());
        $classBuilder = $builder->class($class->name()->short());


        $interfaceType = TypeFactory::reflectedClass($this->reflector, $interfaceFQN);
        $interfaceType = $interfaceType->toLocalType($class->scope());

        $property = $classBuilder->property('inner')
            ->visibility(Visibility::PRIVATE)
            ->type($interfaceType->toPhpString())
            ->docType($interfaceType->__toString());

        $constructor = $classBuilder->method('__construct');
        $constructor->parameter('inner')->type($interfaceType->toPhpString());
        $constructor->body()->line('$this->inner = $inner;');

        $interface = $this->reflector->reflectInterface($interfaceFQN);
        foreach ($interface->methods() as $interfaceMethod) {
            $method = $classBuilder->method($interfaceMethod->name());

            if ($interfaceMethod->returnType()->isDefined()) {
                $method->returnType($interfaceMethod->returnType()->toLocalType($class->scope())->toPhpString());
                foreach ($interfaceMethod->returnType()->expandTypes()->classLike() as $type) {
                    $builder->use($type->name());
                }
            }
            $method->visibility($interfaceMethod->visibility());

            $this->attachParameters($builder, $class, $method, $interfaceMethod);

            $method->body()->line($this->generateMethodBody($interfaceMethod));
        }

        return $this->updater->textEditsFor($builder->build(), Code::fromString((string) $source));
    }

    /**
     * Copying over the method parameters from the interface to the decoration
     */
    private function attachParameters(SourceCodeBuilder $builder, ReflectionClassLike $class, MethodBuilder $method, ReflectionMethod $interfaceMethod): void
    {
        foreach ($interfaceMethod->parameters() as $interfaceMethodParameter) {
            $parameter = $method->parameter($interfaceMethodParameter->name())
                ->type($interfaceMethodParameter->type()->toLocalType($class->scope())->toPhpString());

            foreach ($interfaceMethodParameter->type()->expandTypes()->classLike() as $type) {
                $builder->use($type->name());
            }

            $defaultValue = $interfaceMethodParameter->default();

            if ($defaultValue->isDefined()) {
                $parameter ->defaultValue($interfaceMethodParameter->default()->value());
            }
        }
    }

    /**
     * This method creates the method body which means copying parameters of the interface method to the body of the function.
     * So if the interface contains:
     *
     * function someFunction(string $a, int $b)
     *
     * then the content of the decoration method needs to be
     *
     * $this->inner->someFunction($a, $b);
     */
    private function generateMethodBody(ReflectionMethod $interfaceMethod): string
    {
        $code = '$this->inner->'.$interfaceMethod->name().'(';
        foreach ($interfaceMethod->parameters() as $interfaceMethodParameter) {
            $code .= '$'.$interfaceMethodParameter->name().', ';
        }
        $code = trim($code, ', ');
        $code .= ');';

        if (!$interfaceMethod->returnType()->isVoid()) {
            $code = 'return '. $code;
        }

        return $code;
    }
}
