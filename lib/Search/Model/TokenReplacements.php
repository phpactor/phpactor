<?php

namespace Phpactor\Search\Model;

use ArrayIterator;
use IteratorAggregate;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Traversable;

/**
 * @implements IteratorAggregate<TokenReplacement>
 */
class TokenReplacements implements IteratorAggregate
{
    /**
     * @var TokenReplacement[]
     */
    private array $replacements;

    public function __construct(TokenReplacement ...$replacements)
    {
        $this->replacements = $replacements;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->replacements);
    }

    public function applyTo(DocumentMatches $matches): TextDocument
    {
        $edits = [];
        foreach ($this->replacements as $replacement) {
            foreach ($matches as $match) {
                foreach ($match->tokens()->byName($replacement->placeholder()) as $token) {
                    $edits[] = TextEdit::create(
                        $token->range->start(),
                        $token->range->length(),
                        $replacement->replacement()
                    );
                }
            }
        }
        return TextDocumentBuilder::fromTextDocument(
            $matches->document()
        )->text(
            TextEdits::fromTextEdits($edits)->apply($matches->document()->__toString())
        )->build();
    }
}
