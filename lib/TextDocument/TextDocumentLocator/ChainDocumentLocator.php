<?php

namespace Phpactor\TextDocument\TextDocumentLocator;

use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextDocumentLocator;

class ChainDocumentLocator implements TextDocumentLocator
{
    /**
     * @var TextDocumentLocator[]
     */
    private array $locators;

    /**
     * @param TextDocumentLocator[] $locators
     */
    public function __construct(array $locators)
    {
        $this->locators = $locators;
    }

    
    public function get(TextDocumentUri $uri): TextDocument
    {
        foreach ($this->locators as $workspace) {
            try {
                return $workspace->get($uri);
            } catch (TextDocumentNotFound $notFound) {
            }
        }

        throw TextDocumentNotFound::fromUri($uri);
    }
}
