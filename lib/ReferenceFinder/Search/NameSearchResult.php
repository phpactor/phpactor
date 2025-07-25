<?php

namespace Phpactor\ReferenceFinder\Search;

use Phpactor\Name\FullyQualifiedName;
use Phpactor\TextDocument\TextDocumentUri;

final class NameSearchResult
{
    private function __construct(
        private NameSearchResultType $type,
        private FullyQualifiedName $name,
        private ?TextDocumentUri $uri = null
    ) {
    }

    /**
     * @param string|FullyQualifiedName $name
     * @param string|NameSearchResultType $type
     */
    public static function create($type, $name, ?TextDocumentUri $uri = null): self
    {
        return new self(
            is_string($type) ? new NameSearchResultType($type) : $type,
            is_string($name) ? FullyQualifiedName::fromString($name) : $name,
            $uri
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
