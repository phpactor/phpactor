<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Response;

class ReplaceFileSourceResponse implements Response
{
    private string $replacementSource;

    private string $path;

    private function __construct(string $path, string $replacementSource)
    {
        $this->replacementSource = $replacementSource;
        $this->path = $path;
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
