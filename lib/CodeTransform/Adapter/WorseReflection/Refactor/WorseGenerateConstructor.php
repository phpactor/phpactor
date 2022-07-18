<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;

use Phpactor\CodeTransform\Domain\Refactor\GenerateConstructor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\WorkspaceEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionObjectCreationExpression;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionArgument;
use Phpactor\WorseReflection\Reflector;

class WorseGenerateConstructor implements GenerateConstructor
{
    private Reflector $reflector;
    private BuilderFactory $factory;
    private Updater $updater;

    public function __construct(Reflector $reflector, BuilderFactory $factory, Updater $updater)
    {
        $this->reflector = $reflector;
        $this->factory = $factory;
        $this->updater = $updater;
    }

    public function generateMethod(TextDocument $document, ByteOffset $offset): WorkspaceEdits
    {
        try {
            $newObject = $this->reflector->reflectNode($document, $offset->toInt());
        } catch (NotFound $notFound) {
            return WorkspaceEdits::none();
        }

        if (!$newObject instanceof ReflectionObjectCreationExpression) {
            return WorkspaceEdits::none();
        }

        // do not support existing arguments
        if ($newObject->arguments()->count() !== 0) {
            return WorkspaceEdits::none();
        }

        if ($newObject->class()->methods()->has('__construct')) {
            return WorkspaceEdits::none();
        }

        $arguments = $newObject->arguments();

        $builder = $this->factory->fromSource($document);
        $class = $builder->class($newObject->class()->name());
        $method = $class->method('__construct');

        foreach ($arguments as $argument) {
            assert($argument instanceof ReflectionArgument);
            $type = $argument->type();
            foreach ($type->classNamedTypes() as $classType) {
                $builder->use($classType->__toString());
            }
            $param = $method->parameter($argument->guessName());
            $param->type($argument->type()->short());
        }

        return new WorkspaceEdits(
            new TextDocumentEdits($document->uri(), $this->updater->textEditsFor($builder->build(), Code::fromString($document)))
        );
    }

}
