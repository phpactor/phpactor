<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Response;

class ReplaceFileSourceResponse implements Response
{
    private function __construct(
        private readonly string $path,
        private readonly string $replacementSource
    ) {
    }

    public static function fromPathAndSource(string $path, string $replacementSource)
    {
        return new self($path, $replacementSource);
    }

    public function name(): string
    {
        return 'replace_file_source';
    }

    public function parameters(): array
    {
        return [
            'path' => $this->path,
            'source' => $this->replacementSource,
        ];
    }

    public function path(): string
    {
        return $this->path;
    }
}
