<?php

namespace Phpactor\TextDocument\TextDocumentLocator;

use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextDocumentLocator;

final class InMemoryDocumentLocator implements TextDocumentLocator
{
    /**
     * @var array<string, TextDocument>
     */
    private array $documents = [];

    /**
     * @param array<string, TextDocument> $textDocuments
     */
    private function __construct(array $textDocuments)
    {
        $this->documents = $textDocuments;
    }

    public function get(TextDocumentUri $uri): TextDocument
    {
        if (isset($this->documents[$uri->__toString()])) {
            return $this->documents[$uri->__toString()];
        }

        throw TextDocumentNotFound::fromUri($uri);
    }

    /**
     * @param TextDocument[] $textDocuments
     */
    public static function fromTextDocuments(array $textDocuments): self
    {
        /** @phpstan-ignore-next-line */
        return new self((array)array_combine(array_map(function (TextDocument $document): string {
            return $document->uri()->__toString();
        }, $textDocuments), array_values($textDocuments)));
    }

    public static function new(): self
    {
        return new self([]);
    }
}
