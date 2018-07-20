<?php

namespace Phpactor\Extension\LanguageServer\Server;

use Phpactor\Extension\LanguageServer\Protocol\TextDocumentItem;
use RuntimeException;

class Workspace
{
    /**
     * @var TextDocumentItem[]
     */
    private $items = [];

    public function get(string $uri): TextDocumentItem
    {
        if (!isset($this->items[$uri])) {
            throw new RuntimeException(sprintf(
                'File "%s" has not been registered',
                $uri
            ));
        }

        return $this->items[$uri];
    }

    public function open(TextDocumentItem $textDocument)
    {
        $this->items[$textDocument->uri] = $textDocument;
    }

    public function update(TextDocumentItem $textDocument, $updatedText)
    {
        if (!isset($this->items[$textDocument->uri])) {
            throw new RuntimeException(sprintf(
                'Unknown document "%s"',
                $textDocument->uri
            ));
        }

        $this->items[$textDocument->uri]->text = $updatedText;
    }
}
