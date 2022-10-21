<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeTransform\Domain\Refactor\SimplifyClassName;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;

class WorseSimplifyClassName implements SimplifyClassName
{
    private Reflector $reflector;

    private BuilderFactory $factory;

    private Parser $parser;

    public function __construct(
        Reflector $reflector,
        BuilderFactory $sourceBuilder,
        Parser $parser = null
    ) {
        $this->reflector = $reflector;
        $this->factory = $sourceBuilder;
        $this->parser = $parser ?: new Parser();
    }

    public function getTextEdits(SourceCode $sourceCode, int $offset): TextDocumentEdits
    {
        $symbolContext = $this->reflector
            ->reflectOffset($sourceCode->__toString(), $offset)
            ->symbolContext();
        $type = $symbolContext->type();

        if (!$type instanceof ClassType) {
            return new TextDocumentEdits($sourceCode->uri(), TextEdits::none());
        }

        $sourceBuilder = $this->factory->fromSource($sourceCode);
        $sourceBuilder->use((string) $type->name());
        $sourceBuilder->build();

        $newClassName = $type->name()->short();
        $position = $symbolContext->symbol()->position();

        return new TextDocumentEdits($sourceCode->uri(), TextEdits::fromTextEdits([
            TextEdit::create($position->start(), $position->end() - $position->start(), $newClassName)
        ]));
    }

    public function canSimplifyClassName(SourceCode $source, int $offset): bool
    {
        $node = $this->parser->parseSourceFile($source->__toString());
        $targetNode = $node->getDescendantNodeAtPosition($offset);

        if ($targetNode instanceof QualifiedName) {
            return $targetNode->isFullyQualifiedName();
        }

        return false;
    }
}
