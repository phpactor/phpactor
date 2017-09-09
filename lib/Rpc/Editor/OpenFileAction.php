<?php

namespace Phpactor\Rpc\Editor;

use Phpactor\Rpc\ActionRequest;
use Phpactor\Rpc\Action;

class OpenFileAction implements Action
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var int
     */
    private $offset;

    private function __construct(string $path, int $offset = 0)
    {
        $this->path = $path;
        $this->offset = $offset;

    }

    public static function fromPath(string $path)
    {
        return new self($path);
    }

    public static function fromPathAndOffset(string $path, int $offset)
    {
        return new self($path, $offset);
    }

    public function name(): string
    {
        return 'open_file';
    }

    public function parameters(): array
    {
        return [
            'path' => $this->path,
            'offset' => $this->offset,
        ];
    }
}

