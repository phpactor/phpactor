<?php

namespace Phpactor\TextDocument;

class Location
{
    public function __construct(private TextDocumentUri $uri, private ByteOffset $offset)
    {
    }

    public static function fromPathAndOffset(string $string, int $int): self
    {
        return new self(
            TextDocumentUri::fromString($string),
            ByteOffset::fromInt($int)
        );
    }

    public function uri(): TextDocumentUri
    {
        return $this->uri;
    }

    public function offset(): ByteOffset
    {
        return $this->offset;
    }
}
