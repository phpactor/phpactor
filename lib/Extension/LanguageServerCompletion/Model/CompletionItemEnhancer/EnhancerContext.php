<?php

namespace Phpactor\Extension\LanguageServerCompletion\Model\CompletionItemEnhancer;

use Phpactor\Completion\Core\Suggestion;
use Phpactor\LanguageServerProtocol\CompletionParams;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\TextDocumentItem;

class EnhancerContext
{
    public ?string $nameImport;
    public ?string $suggestionType;
    public TextDocumentItem $textDocument;

    public Position $position;


    public function __construct(TextDocumentItem $textDocument, Position $position, ?string $nameImport, ?string $suggestionType)
    {
        $this->nameImport = $nameImport;
        $this->suggestionType = $suggestionType;
        $this->textDocument = $textDocument;
        $this->position = $position;

    }
    public static function fromParamsAndSuggestion(TextDocumentItem $item, Position $position, Suggestion $suggestion): self
    {
        return new self($item, $position, $suggestion->nameImport(), $suggestion->type());
    }
}
