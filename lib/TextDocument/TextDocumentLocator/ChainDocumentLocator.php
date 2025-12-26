<?php

namespace Phpactor\TextDocument\TextDocumentLocator;

use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextDocumentLocator;

class ChainDocumentLocator implements TextDocumentLocator
{
    /**
     * @param TextDocumentLocator[] $locators
     */
    public function __construct(private readonly array $locators)
    {
    }


    public function get(TextDocumentUri $uri): TextDocument
    {
        foreach ($this->locators as $workspace) {
            try {
                return $workspace->get($uri);
            } catch (TextDocumentNotFound) {
            }
        }

        throw TextDocumentNotFound::fromUri($uri);
    }
}
