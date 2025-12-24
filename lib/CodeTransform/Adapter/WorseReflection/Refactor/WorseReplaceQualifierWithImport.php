<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\AstProvider;
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

class WorseReplaceQualifierWithImport implements ReplaceQualifierWithImport
{
    public function __construct(
        private Reflector $reflector,
        private BuilderFactory $factory,
        private Updater $updater,
        private AstProvider $parser = new TolerantAstProvider(),
    ) {
    }

    public function getTextEdits(SourceCode $sourceCode, int $offset): TextDocumentEdits
    {
        $nodeContext = $this->reflector
            ->reflectOffset($sourceCode, $offset)
            ->nodeContext();
        $type = $nodeContext->type();

        if (!$type instanceof ClassType) {
            return new TextDocumentEdits($sourceCode->uri(), TextEdits::none());
        }

        $textEdits = $this->getTextEditForImports($sourceCode, $type);

        $newClassName = $type->name()->short();
        $position = $nodeContext->symbol()->position();

        return new TextDocumentEdits(
            $sourceCode->uri(),
            $textEdits->merge(TextEdits::fromTextEdits([
                TextEdit::create(
                    $position->start()->toInt(),
                    $position->end()->toInt() - $position->start()->toInt(),
                    $newClassName
                )
            ]))
        );
    }

    public function canReplaceWithImport(SourceCode $source, int $offset): bool
    {
        $node = $this->parser->get($source->__toString());
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
