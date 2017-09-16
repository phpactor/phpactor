<?php

namespace Phpactor\Rpc\Editor;

use Phpactor\Rpc\Action;

class CloseFileAction implements Action
{
    /**
     * @var string
     */
    private $path;

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
