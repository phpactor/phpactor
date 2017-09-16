<?php

namespace Phpactor\Rpc\Editor;

use Phpactor\Rpc\Action;

class ReplaceFileSourceAction implements Action
{
    /**
     * @var string
     */
    private $replacementSource;

    /**
     * @var string
     */
    private $path;

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

    public function replacementSource(): string
    {
        return $this->replacementSource;
    }

    public function path(): string
    {
        return $this->path;
    }
}

