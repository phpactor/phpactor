<?php

namespace Phpactor\ReferenceFinder\Search;

use Phpactor\Name\FullyQualifiedName;
use Phpactor\TextDocument\TextDocumentUri;

final class NameSearchResult
{
    private NameSearchResultType $type;

    private FullyQualifiedName $name;

    private ?TextDocumentUri $uri;

    private function __construct(NameSearchResultType $type, FullyQualifiedName $name, ?TextDocumentUri $uri = null)
    {
        $this->type = $type;
        $this->name = $name;
        $this->uri = $uri;
    }

    /**
     * @param string|FullyQualifiedName $name
     * @param string|NameSearchResultType $type
     * @param string|TextDocumentUri $uri
     */
    public static function create($type, $name, $uri = null): self
    {
        return new self(
            is_string($type) ? new NameSearchResultType($type) : $type,
            is_string($name) ? FullyQualifiedName::fromString($name) : $name,
            $uri ? (is_string($uri) ? TextDocumentUri::fromString($uri) : $uri) : null
        );
    }

    public function name(): FullyQualifiedName
    {
        return $this->name;
    }

    public function type(): NameSearchResultType
    {
        return $this->type;
    }

    public function uri(): ?TextDocumentUri
    {
        return $this->uri;
    }
}
