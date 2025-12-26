<?php

namespace Phpactor\TextDocument;

class Location
{
    public function __construct(
        private readonly TextDocumentUri $uri,
        private readonly ByteOffsetRange $range
    ) {
    }

    public static function fromPathAndOffsets(string $path, int $start, int $end): self
    {
        return new self(
            TextDocumentUri::fromString($path),
            ByteOffsetRange::fromInts($start, $end),
        );
    }

    public function uri(): TextDocumentUri
    {
        return $this->uri;
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }
}
