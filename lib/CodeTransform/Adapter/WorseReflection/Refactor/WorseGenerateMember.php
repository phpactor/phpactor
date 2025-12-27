<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Phpactor\CodeBuilder\Domain\Builder\ClassBuilder;
use Phpactor\CodeBuilder\Domain\Builder\EnumBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode as PhpactorSourceCode;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeTransform\Domain\Refactor\GenerateMember;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionStaticMemberAccess;
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
use RuntimeException;

class WorseGenerateMember implements GenerateMember
{
    public function __construct(
        private readonly Reflector $reflector,
        private readonly BuilderFactory $factory,
        private readonly Updater $updater
    ) {
    }

    public function generateMember(SourceCode $sourceCode, int $offset, ?string $methodName = null): TextDocumentEdits
    {
        $contextType = $this->contextType($sourceCode, $offset);
        $worseSourceCode = TextDocumentBuilder::fromPathAndString((string) $sourceCode->uri()->path(), (string) $sourceCode);
        $memberAccess = $this->reflector->reflectNode($worseSourceCode, $offset);

        if ($memberAccess instanceof ReflectionMethodCall) {
            $this->validate($memberAccess);
            $visibility = $this->determineVisibility($contextType, $memberAccess->class());

            $prototype = $this->addMethodCallToBuilder($memberAccess, $visibility, $memberAccess->isStatic(), $methodName);
            $sourceCode = $this->resolveSourceCode($sourceCode, $memberAccess->class(), $visibility);

            $textEdits = $this->updater->textEditsFor($prototype, Code::fromString((string) $sourceCode));

            return new TextDocumentEdits(TextDocumentUri::fromString($sourceCode->uri()->path()), $textEdits);
        }

        if ($memberAccess instanceof ReflectionStaticMemberAccess) {
            $visibility = $this->determineVisibility($contextType, $memberAccess->class());
            $prototype = $this->addMemberToBuilder($memberAccess, $visibility, $methodName);
            $sourceCode = $this->resolveSourceCode($sourceCode, $memberAccess->class(), $visibility);

            $textEdits = $this->updater->textEditsFor($prototype, Code::fromString((string) $sourceCode));

            return new TextDocumentEdits(TextDocumentUri::fromString($sourceCode->uri()->path()), $textEdits);
        }

        throw new RuntimeException(sprintf(
            'Could not generate member for "%s"',
            $memberAccess::class
        ));
    }

    private function resolveSourceCode(SourceCode $sourceCode, ReflectionClassLike $class, string $visibility): SourceCode
    {
        $containerSourceCode = SourceCode::fromStringAndPath(
            (string) $class->sourceCode(),
            $class->sourceCode()->uri()?->path()
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

        $classBuilder = $builder->classLike($reflectionClass->name()->short());
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

    private function addMemberToBuilder(
        ReflectionStaticMemberAccess $access,
        Visibility $visibility,
        ?string $caseName
    ):  PhpactorSourceCode {
        $caseName = $caseName ?: $access->name();

        $reflectionClass = $access->class();
        $builder = $this->factory->fromSource($reflectionClass->sourceCode());

        $classLikeBuilder = $builder->classLike($reflectionClass->name()->short());
        if ($classLikeBuilder instanceof EnumBuilder) {
            $classLikeBuilder->case($caseName);
        }
        if ($classLikeBuilder instanceof ClassBuilder) {
            $constantBuuilder = $classLikeBuilder->constant($caseName, 0);
            $constantBuuilder->visibility($visibility);
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
