<?php

namespace Phpactor\Rpc\Editor;

use Phpactor\Rpc\Response;

class OpenFileAction implements Response
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

    public function path(): string
    {
        return $this->path;
    }
}
