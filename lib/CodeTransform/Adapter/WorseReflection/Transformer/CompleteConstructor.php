<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Amp\Promise;
use Amp\Success;
use Generator;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;

class CompleteConstructor implements Transformer
{
    public function __construct(
        private Reflector $reflector,
        private Updater $updater,
        private string $visibility,
        private bool $promote = false
    ) {
    }

    /**
        * @return Promise<TextEdits>
     */
    public function transform(SourceCode $source): Promise
    {
        if (false === $this->promote) {
            return new Success($this->transformAssign($source));
        }

        return new Success($this->transformPromote($source));
    }


    /**
        * @return Promise<Diagnostics>
     */
    public function diagnostics(SourceCode $source): Promise
    {
        $diagnostics = [];
        foreach ($this->candidateClasses($source) as $class) {
            $constructMethod = $class->methods()->belongingTo($class->name())->get('__construct');
            assert($constructMethod instanceof ReflectionMethod);
            foreach ($constructMethod->parameters()->notPromoted() as $parameter) {
                assert($parameter instanceof ReflectionParameter);
                $frame = $constructMethod->frame();

                $isUsed = $frame->locals()->byName($parameter->name())->count() > 0;
                $hasProperty = $class->properties()->has($parameter->name());

                if ($isUsed && $hasProperty) {
                    continue;
                }

                $diagnostics[] = new Diagnostic(
                    ByteOffsetRange::fromInts(
                        $parameter->position()->start()->toInt(),
                        $parameter->position()->end()->toInt() + 5 + strlen($class->name()->__toString())
                    ),
                    sprintf(
                        'Parameter "%s" may not have been assigned',
                        $parameter->name()
                    ),
                    Diagnostic::WARNING
                );
            }
        }

        return new Success(new Diagnostics($diagnostics));
    }

    private function transformAssign(SourceCode $source): TextEdits
    {
        $edits = [];
        $sourceCodeBuilder = SourceCodeBuilder::create();

        foreach ($this->candidateClasses($source) as $class) {
            $classBuilder = $sourceCodeBuilder->class($class->name()->short());
            $methodBuilder = $classBuilder->method('__construct');
            $constructMethod = $class->methods()->get('__construct');
            $methodBody = (string) $constructMethod->body();

            // Filtering out parameters from the parent class
            $nonPromotedParameterNames = $this->getParentClassParamaterNames($class);
            $parametersToHandle = array_filter(
                iterator_to_array($constructMethod->parameters()->notPromoted()),
                fn (ReflectionParameter $parameter) => !in_array($parameter->name(), $nonPromotedParameterNames)
            );

            foreach ($parametersToHandle as $parameter) {
                if (preg_match('{this\s*->' . $parameter->name() . '}', $methodBody)) {
                    continue;
                }
                $methodBuilder->body()->line('$this->' . $parameter->name() . ' = $' . $parameter->name() .';');
            }

            foreach ($parametersToHandle as $parameter) {
                if ($parameter->isPromoted()) {
                    continue;
                }

                assert($parameter instanceof ReflectionParameter);
                if (true === $class->properties()->has($parameter->name())) {
                    continue;
                }

                $propertyBuilder = $classBuilder->property($parameter->name());
                $propertyBuilder->visibility($this->visibility);
                $parameterType = $parameter->inferredType();
                if ($parameterType->isDefined()) {
                    $parameterType = $parameterType->toLocalType($class->scope());
                    $propertyBuilder->type($parameterType->toPhpString(), $parameterType);
                    $propertyBuilder->docType((string)$parameterType);
                }
            }
        }

        return $this->updater->textEditsFor($sourceCodeBuilder->build(), Code::fromString((string) $source));
    }

    private function transformPromote(SourceCode $source): TextEdits
    {
        $edits = [];

        foreach ($this->candidateClasses($source) as $class) {
            $constructMethod = $class->methods()->get('__construct');
            $nonPromotedParameterNames = $this->getParentClassParamaterNames($class);
            foreach ($constructMethod->parameters()->notPromoted() as $parameter) {
                if (in_array($parameter->name(), $nonPromotedParameterNames)) {
                    continue;
                }
                $edits[] = TextEdit::create($parameter->position()->start()->toInt(), 0, sprintf('%s ', $this->visibility));
            }
        }

        return TextEdits::fromTextEdits($edits);
    }

    /**
    * Get the names of the parent constructor to know which parameters should not be promoted.
    * @return array<string>
    */
    private function getParentClassParamaterNames(ReflectionClass $class): array
    {
        $ancestor = $class->parent();
        if ($ancestor === null) {
            return [];
        }

        if (!$ancestor->methods()->has('__construct')) {
            return [];
        }

        $parameters = [];
        foreach($ancestor->methods()->get('__construct')->parameters() as $parameter) {
            $parameters[] = $parameter->name();
        }

        return $parameters;
    }

    /**
     * @return Generator<ReflectionClass>
     */
    private function candidateClasses(SourceCode $source): Generator
    {
        $classes = $this->reflector->reflectClassesIn($source)->classes();
        foreach ($classes as $class) {
            if ($class instanceof ReflectionInterface) {
                continue;
            }

            if (!$class->methods()->belongingTo($class->name())->has('__construct')) {
                continue;
            }

            yield $class;
        }
    }
}
