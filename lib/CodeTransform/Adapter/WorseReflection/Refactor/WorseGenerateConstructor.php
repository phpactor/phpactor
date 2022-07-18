<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;

use Phpactor\CodeTransform\Domain\Refactor\GenerateConstructor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextDocumentUri;
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

    private Parser $parser;

    public function __construct(Reflector $reflector, BuilderFactory $factory, Updater $updater, Parser $parser)
    {
        $this->reflector = $reflector;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->parser = $parser;
    }

    public function generateMethod(TextDocument $document, ByteOffset $offset): WorkspaceEdits
    {
        $node = $this->parser->parseSourceFile($document->__toString())->getDescendantNodeAtPosition($offset->toInt());
        $node = $node->getFirstAncestor(ObjectCreationExpression::class);

        if (!$node instanceof ObjectCreationExpression) {
            return WorkspaceEdits::none();
        }
        try {
            $newObject = $this->reflector->reflectNode($document, $node->getStartPosition());
        } catch (NotFound $notFound) {
            return WorkspaceEdits::none();
        }

        if (!$newObject instanceof ReflectionObjectCreationExpression) {
            return WorkspaceEdits::none();
        }

        try {
            if ($newObject->class()->methods()->has('__construct')) {
                return WorkspaceEdits::none();
            }
        } catch (NotFound $notFound) {
            return WorkspaceEdits::none();
        }

        $arguments = $newObject->arguments();

        $builder = $this->factory->fromSource($newObject->class()->sourceCode());
        $class = $builder->class($newObject->class()->name()->short());
        $method = $class->method('__construct');

        foreach ($arguments->named() as $name => $argument) {
            assert($argument instanceof ReflectionArgument);
            $type = $argument->type();
            foreach ($type->classNamedTypes() as $classType) {
                $builder->use($classType->__toString());
            }
            $param = $method->parameter($name);
            $param->type($argument->type()->short());
        }

        return new WorkspaceEdits(
            new TextDocumentEdits(
                TextDocumentUri::fromString($newObject->class()->sourceCode()->mustGetUri()),
                $this->updater->textEditsFor($builder->build(), Code::fromString($newObject->class()->sourceCode()))
            )
        );
    }
}
