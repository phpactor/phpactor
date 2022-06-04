<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\AssignmentToMissingPropertyDiagnostic;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Builder\ClassLikeBuilder;
use Phpactor\CodeBuilder\Domain\Builder\ClassBuilder;
use Phpactor\CodeBuilder\Domain\Builder\TraitBuilder;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

class AddMissingProperties implements Transformer
{
    private const LENGTH_OF_THIS_PREFIX = 7;

    private Reflector $reflector;

    private Updater $updater;

    private Parser $parser;

    public function __construct(Reflector $reflector, Updater $updater, ?Parser $parser = null)
    {
        $this->updater = $updater;
        $this->reflector = $reflector;
        $this->parser = $parser ?: new Parser();
    }

    public function transform(SourceCode $code): TextEdits
    {
        $rootNode = $this->parser->parseSourceFile($code->__toString());
        $wrDiagnostics = $this->reflector->diagnostics($code->__toString());
        $sourceBuilder = SourceCodeBuilder::create();

        /** @var AssignmentToMissingPropertyDiagnostic $diagnostic */
        foreach ($wrDiagnostics->byClass(AssignmentToMissingPropertyDiagnostic::class) as $diagnostic) {
            $class = $this->reflector->reflectClassLike($diagnostic->classType());
            $classBuilder = $this->resolveClassBuilder($sourceBuilder, $class);
            $type = $diagnostic->propertyType();

            $propertyBuilder = $classBuilder
                ->property($diagnostic->propertyName())
                ->visibility('private');

            if ($type->isDefined()) {
                foreach ($type->classNamedTypes() as $importClass) {
                    $sourceBuilder->use($importClass->name()->__toString());
                }
                $type = $type->toLocalType($class->scope());
                $propertyBuilder->type($type->toPhpString(), $type);
                $propertyBuilder->docType((string)$type->generalize());

                if ($diagnostic->isSubscriptAssignment()) {
                    $propertyBuilder->defaultValue([]);
                }
            }
        }

        if (isset($class)) {
            $sourceBuilder->namespace($class->name()->namespace());
        }

        return $this->updater->textEditsFor(
            $sourceBuilder->build(),
            Code::fromString((string) $code)
        );
    }

    public function diagnostics(SourceCode $code): Diagnostics
    {
        $wrDiagnostics = $this->reflector->diagnostics($code->__toString());
        $diagnostics = [];

        /** @var AssignmentToMissingPropertyDiagnostic $diagnostic */
        foreach ($wrDiagnostics->byClass(AssignmentToMissingPropertyDiagnostic::class) as $diagnostic) {
            $diagnostics[] = new Diagnostic(
                $diagnostic->range(),
                $diagnostic->message(),
                Diagnostic::WARNING
            );
        }

        return new Diagnostics($diagnostics);
    }

    /**
     * @return TraitBuilder|ClassBuilder
     */
    private function resolveClassBuilder(SourceCodeBuilder $sourceBuilder, ReflectionClassLike $class): ClassLikeBuilder
    {
        $name = $class->name()->short();

        if ($class->isTrait()) {
            return $sourceBuilder->trait($name);
        }

        return $sourceBuilder->class($name);
    }
}
