<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Amp\Promise;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\AssignmentToMissingPropertyDiagnostic;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Builder\ClassLikeBuilder;
use Phpactor\CodeBuilder\Domain\Builder\ClassBuilder;
use Phpactor\CodeBuilder\Domain\Builder\TraitBuilder;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use function Amp\call;

class AddMissingProperties implements Transformer
{
    private const LENGTH_OF_THIS_PREFIX = 7;

    public function __construct(
        private Reflector $reflector,
        private Updater $updater,
        private AstProvider $parser = new TolerantAstProvider(),
    ) {
    }

    /**
     * @return Promise<TextEdits>
     */
    public function transform(SourceCode $code): Promise
    {
        return call(function () use ($code) {
            $rootNode = $this->parser->get($code->__toString());
            $wrDiagnostics = yield $this->reflector->diagnostics($code);
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
                    foreach ($type->allTypes()->classLike() as $importClass) {
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
        });
    }

    /**
     * @return Promise<Diagnostics>
     */
    public function diagnostics(SourceCode $code): Promise
    {
        return call(function () use ($code) {
            $wrDiagnostics = yield $this->reflector->diagnostics($code);
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
        });
    }

    /**
     * @return TraitBuilder|ClassBuilder
     */
    private function resolveClassBuilder(SourceCodeBuilder $sourceBuilder, ReflectionClassLike $class): ClassLikeBuilder
    {
        $name = $class->name()->short();

        if ($class instanceof ReflectionTrait) {
            return $sourceBuilder->trait($name);
        }

        return $sourceBuilder->class($name);
    }
}
