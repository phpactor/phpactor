<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Response;

class CloseFileResponse implements Response
{
    private string $path;

    private function __construct(string $path)
    {
        $this->path = $path;
    }

    public static function fromPath(string $path)
    {
        return new self($path);
    }

    public function name(): string
    {
        return 'close_file';
    }

    public function parameters(): array
    {
        return [
            'path' => $this->path,
        ];
    }

    public function path(): string
    {
        return $this->path;
    }
}
