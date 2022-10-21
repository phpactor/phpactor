<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Domain\Refactor\ExpandClass;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;

class WorseExpandClass implements ExpandClass
{
    private Reflector $reflector;

    private Parser $parser;

    public function __construct(
        Reflector $reflector,
        Parser $parser = null
    ) {
        $this->reflector = $reflector;
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

        $newClassName = $type->name()->__toString();
        $position = $symbolContext->symbol()->position();

        return new TextDocumentEdits($sourceCode->uri(), TextEdits::fromTextEdits([
            TextEdit::create($position->start(), $position->end() - $position->start(), $newClassName)
        ]));
    }

    public function canExpandClassName(SourceCode $source, int $offset): bool
    {
        $node = $this->parser->parseSourceFile($source->__toString());
        $targetNode = $node->getDescendantNodeAtPosition($offset);

        if (!$targetNode instanceof QualifiedName) {
            return false;
        }

        return $targetNode->isRelativeName();
    }
}
