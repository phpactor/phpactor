<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Generator;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode as WorseSourceCode;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;

class CompleteConstructor implements Transformer
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var Updater
     */
    private $updater;

    public function __construct(Reflector $reflector, Updater $updater)
    {
        $this->updater = $updater;
        $this->reflector = $reflector;
    }

    public function transform(SourceCode $source): TextEdits
    {
        $edits = [];
        $sourceCodeBuilder = SourceCodeBuilder::create();

        foreach ($this->candidateClasses($source) as $class) {
            $classBuilder = $sourceCodeBuilder->class($class->name()->short());
            $methodBuilder = $classBuilder->method('__construct');
            $constructMethod = $class->methods()->get('__construct');
            $methodBody = (string) $constructMethod->body();

            foreach ($constructMethod->parameters()->notPromoted() as $parameter) {
                if (preg_match('{this\s*->' . $parameter->name() . '}', $methodBody)) {
                    continue;
                }
                $methodBuilder->body()->line('$this->' . $parameter->name() . ' = $' . $parameter->name() .';');
            }

            foreach ($constructMethod->parameters()->notPromoted() as $parameter) {
                if ($parameter->isPromoted()) {
                    continue;
                }

                assert($parameter instanceof ReflectionParameter);
                if (true === $class->properties()->has($parameter->name())) {
                    continue;
                }


                $propertyBuilder = $classBuilder->property($parameter->name());
                $propertyBuilder->visibility('private');
                $parameterType = $parameter->type();
                if ($parameterType->isDefined()) {
                    $typeName = (string) $parameter->type()->short();
                    $className = $parameterType->className();
                    if ($className) {
                        $typeName = $class->scope()->resolveLocalName($className)->__toString();
                    }

                    if ($parameterType->isNullable()) {
                        $typeName = '?' . $typeName;
                    }

                    $propertyBuilder->type($typeName);
                }
            }
        }

        return $this->updater->textEditsFor($sourceCodeBuilder->build(), Code::fromString((string) $source));
    }

    /**
     * {@inheritDoc}
     */
    public function diagnostics(SourceCode $source): Diagnostics
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
                        $parameter->position()->start(),
                        $parameter->position()->end() + 5 + strlen($class->name()->__toString())
                    ),
                    sprintf(
                        'Parameter "%s" may not have been assigned',
                        $parameter->name()
                    ),
                    Diagnostic::WARNING
                );
            }
        }

        return new Diagnostics($diagnostics);
    }

    /**
     * @return Generator<ReflectionClass>
     */
    private function candidateClasses(SourceCode $source): Generator
    {
        $classes = $this->reflector->reflectClassesIn(WorseSourceCode::fromString((string) $source));
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
