<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode as PhpactorSourceCode;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeTransform\Domain\Refactor\GenerateMethod;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionArgument;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeBuilder\Domain\BuilderFactory;

class WorseGenerateMethod implements GenerateMethod
{
    public function __construct(
        private Reflector $reflector,
        private BuilderFactory $factory,
        private Updater $updater
    ) {
    }

    public function generateMethod(SourceCode $sourceCode, int $offset, ?string $methodName = null): TextDocumentEdits
    {
        $contextType = $this->contextType($sourceCode, $offset);
        $worseSourceCode = TextDocumentBuilder::fromPathAndString((string) $sourceCode->uri()->path(), (string) $sourceCode);
        $methodCall = $this->reflector->reflectMethodCall($worseSourceCode, $offset);

        $this->validate($methodCall);
        $visibility = $this->determineVisibility($contextType, $methodCall->class());

        $prototype = $this->addMethodCallToBuilder($methodCall, $visibility, $methodCall->isStatic(), $methodName);
        $sourceCode = $this->resolveSourceCode($sourceCode, $methodCall, $visibility);

        $textEdits = $this->updater->textEditsFor($prototype, Code::fromString((string) $sourceCode));

        return new TextDocumentEdits(TextDocumentUri::fromString($sourceCode->uri()->path()), $textEdits);
    }

    private function resolveSourceCode(SourceCode $sourceCode, ReflectionMethodCall $methodCall, string $visibility): SourceCode
    {
        $containerSourceCode = SourceCode::fromStringAndPath(
            (string) $methodCall->class()->sourceCode(),
            $methodCall->class()->sourceCode()->uri()?->path()
        );

        if ($sourceCode->uri()->path() != $containerSourceCode->uri()->path()) {
            return $containerSourceCode;
        }

        return $sourceCode;
    }

    private function contextType(SourceCode $sourceCode, int $offset): ?Type
    {
        $worseSourceCode = TextDocumentBuilder::fromPathAndString((string) $sourceCode->uri()->path(), (string) $sourceCode);
        $reflectionOffset = $this->reflector->reflectOffset($worseSourceCode, $offset);

        /**
         * @var Variable $variable
         */
        foreach ($reflectionOffset->frame()->locals()->byName('$this') as $variable) {
            return $variable->type();
        }

        return null;
    }

    private function addMethodCallToBuilder(
        ReflectionMethodCall $methodCall,
        Visibility $visibility,
        bool $static,
        ?string $methodName
    ):  PhpactorSourceCode {
        $methodName = $methodName ?: $methodCall->name();

        $reflectionClass = $methodCall->class();
        $builder = $this->factory->fromSource($reflectionClass->sourceCode());

        $classBuilder = $reflectionClass->isClass() ?
            $builder->class($reflectionClass->name()->short()) :
            $builder->interface($reflectionClass->name()->short());

        $methodBuilder = $classBuilder->method($methodName);
        $methodBuilder->visibility((string) $visibility);
        if ($static) {
            $methodBuilder->static();
        }

        $docblockTypes = [];

        /** @var ReflectionArgument $argument */
        foreach ($methodCall->arguments()->named() as $name => $argument) {
            $type = $argument->type();

            if ($type->isAugmented()) {
                $docblockTypes[$name] = $type->toLocalType($reflectionClass->scope());
            }

            $parameterBuilder = $methodBuilder->parameter($name);

            if ($type->isDefined()) {
                $parameterBuilder->type($type->short(), $type);

                foreach ($type->allTypes()->classLike() as $classType) {
                    $builder->use($classType->toPhpString());
                }
            }
        }

        // TODO: this should be handled by the code updater (e.g. $docblock->addParam(new ParamPrototype(...)))
        $docblock = [];
        foreach ($docblockTypes as $name => $type) {
            $docblock[] = sprintf('@param %s $%s', $type->__toString(), $name);
        }

        if ($docblock) {
            $methodBuilder->docblock(implode("\n", $docblock));
        }


        $inferredType = $methodCall->inferredReturnType();
        if ($inferredType->isDefined()) {
            $methodBuilder->returnType($inferredType->toPhpString(), $inferredType);
            // this will not render localized types see https://github.com/phpactor/phpactor/issues/1453
            // if ($inferredType->__toString() !== $inferredType->toPhpString()) {
            //     $methodBuilder->docblock('@return ' . $inferredType->__toString());
            // }
        }

        return $builder->build();
    }

    private function determineVisibility(?Type $contextType, ReflectionClassLike $targetClass): Visibility
    {
        if (null === $contextType) {
            return Visibility::public();
        }

        if ($contextType instanceof ClassType && $contextType->name() == $targetClass->name()) {
            return Visibility::private();
        }

        return Visibility::public();
    }

    private function validate(ReflectionMethodCall $methodCall): void
    {
        $target = $methodCall->class();
        if (!$target instanceof ReflectionClass && !$target instanceof ReflectionInterface && !$target instanceof ReflectionEnum) {
            throw new TransformException(sprintf(
                'Can only generate methods on classes or interfaces (trying on %s)',
                get_class($target->name())
            ));
        }
    }
}
