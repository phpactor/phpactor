<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Attribute;
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
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ClassInvocation;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionArgument;
use Phpactor\WorseReflection\Core\Reflection\ReflectionNode;
use Phpactor\WorseReflection\Reflector;

class WorseGenerateConstructor implements GenerateConstructor
{
    public function __construct(
        private Reflector $reflector,
        private BuilderFactory $factory,
        private Updater $updater,
        private Parser $parser
    ) {
    }

    public function generateMethod(TextDocument $document, ByteOffset $offset): WorkspaceEdits
    {
        $reflectionNode = $this->reflectionNode($document, $offset);

        if (null === $reflectionNode) {
            return WorkspaceEdits::none();
        }

        if (!$reflectionNode instanceof ClassInvocation) {
            return WorkspaceEdits::none();
        }

        try {
            if ($reflectionNode->class()->methods()->has('__construct')) {
                return WorkspaceEdits::none();
            }
        } catch (NotFound) {
            return WorkspaceEdits::none();
        }

        $arguments = $reflectionNode->arguments();

        if (count($arguments) === 0) {
            return WorkspaceEdits::none();
        }

        $builder = $this->factory->fromSource($reflectionNode->class()->sourceCode());
        $class = $builder->class($reflectionNode->class()->name()->short());
        $method = $class->method('__construct');

        foreach ($arguments->named() as $name => $argument) {
            assert($argument instanceof ReflectionArgument);
            $type = $argument->type();
            foreach ($type->allTypes()->classLike() as $classType) {
                $builder->use($classType->__toString());
            }
            $param = $method->parameter($name);
            $param->type($argument->type()->short());
        }

        return new WorkspaceEdits(
            new TextDocumentEdits(
                TextDocumentUri::fromString($reflectionNode->class()->sourceCode()->mustGetUri()),
                $this->updater->textEditsFor($builder->build(), Code::fromString($reflectionNode->class()->sourceCode()))
            )
        );
    }

    private function reflectionNode(TextDocument $document, ByteOffset $offset): ?ReflectionNode
    {
        $node = $this->node($document, $offset);
        if (null === $node) {
            return null;
        }

        try {
            $newObject = $this->reflector->reflectNode($document, $node->getStartPosition());
        } catch (NotFound) {
            return null;
        }

        return $newObject;
    }

    private function node(TextDocument $document, ByteOffset $offset): ?Node
    {
        $node = $this->parser->parseSourceFile($document->__toString())->getDescendantNodeAtPosition($offset->toInt());

        if ($node->parent instanceof Attribute) {
            return $node->parent;
        }

        $node = $node->getFirstAncestor(ObjectCreationExpression::class);

        if ($node instanceof ObjectCreationExpression) {
            return $node;
        }

        return null;
    }
}
