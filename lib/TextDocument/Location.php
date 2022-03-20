<?php

namespace Phpactor\TextDocument;

class Location
{
    private TextDocumentUri $uri;
    
    private ByteOffset $offset;

    public function __construct(TextDocumentUri $uri, ByteOffset $offset)
    {
        $this->uri = $uri;
        $this->offset = $offset;
    }

    public static function fromPathAndOffset(string $string, int $int): Location
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
