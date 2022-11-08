<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Refactor;

use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeTransform\Domain\Refactor\ReplaceQualifierWithImport;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\NameImporter;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;
use Webmozart\Assert\Assert;

class WorseReplaceQualifierWithImport implements ReplaceQualifierWithImport
{
    private Parser $parser;

    public function __construct(
        private Reflector $reflector,
        private NameImporter $nameImporter,
        private ClientApi $client,
        Parser $parser = null
    ) {
        $this->parser = $parser ?: new Parser();
    }

    public function getTextEdits(TextDocumentItem $document, int $offset): TextDocumentEdits
    {
        $symbolContext = $this->reflector
            ->reflectOffset($document->text, $offset)
            ->symbolContext();
        $type = $symbolContext->type();

        if (!$type instanceof ClassType) {
            return new TextDocumentEdits($document->uri, TextEdits::none());
        }

        $position = $symbolContext->symbol()->position();

        $result = $this->nameImporter->__invoke(
            $document,
            $position->start(),
            'class',
            (string) $type->name(),
            false
        );

        Assert::true($result->isSuccess());

        return $this->client->workspace()->applyEdit(new WorkspaceEdit([
            $uri => $result->getTextEdits() ?? []
        ]), 'Import class');
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
}
