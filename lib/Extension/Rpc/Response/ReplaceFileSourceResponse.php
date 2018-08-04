<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Diff\TextEditBuilder;
use Phpactor\Extension\Rpc\Response;

class ReplaceFileSourceResponse implements Response
{
    /**
     * @var string
     */
    private $replacementSource;

    /**
     * @var string
     */
    private $path;

    private function __construct(string $path, string $originalSource, string $replacementSource)
    {
        $this->replacementSource = $replacementSource;
        $this->path = $path;
    }

    public static function fromPathOldAndNewSource(string $path, string $originalSource, string $replacementSource)
    {
        return new self($path, $originalSource, $replacementSource);
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

    public function newSource(): string
    {
        return $this->newSource;
    }
}
