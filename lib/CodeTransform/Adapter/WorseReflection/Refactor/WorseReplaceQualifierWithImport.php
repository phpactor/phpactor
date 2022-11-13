<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeTransform\Domain\Refactor\ReplaceQualifierWithImport;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeTransform\Domain\Refactor\ImportName;

class WorseReplaceQualifierWithImport implements ReplaceQualifierWithImport
{
    private Parser $parser;

    public function __construct(
        private Reflector $reflector,
        private ImportName $nameImporter,
        private BuilderFactory $factory,
        private Updater $updater,
        Parser $parser = null
    ) {
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

        $textEdits = $this->getTextEditForImports($sourceCode, $type);

        $newClassName = $type->name()->short();
        $position = $symbolContext->symbol()->position();

        return new TextDocumentEdits(
            $sourceCode->uri(),
            $textEdits->merge(TextEdits::fromTextEdits([
                TextEdit::create($position->start(), $position->end() - $position->start(), $newClassName)
            ]))
        );
    }

    public function canReplaceWithImport(SourceCode $source, int $offset): bool
    {
        $node = $this->parser->parseSourceFile($source->__toString());
        $targetNode = $node->getDescendantNodeAtPosition($offset);

        if ($targetNode instanceof QualifiedName) {
            return $targetNode->isFullyQualifiedName();
        }

        return false;
    }

    private function getTextEditForImports(SourceCode $sourceCode, ClassType $type): TextEdits
    {
        $sourceBuilder = $this->factory->fromSource($sourceCode);
        $sourceBuilder->use((string) $type->name());

        return $this->updater->textEditsFor(
            $sourceBuilder->build(),
            Code::fromString((string) $sourceCode)
        );
    }
}
