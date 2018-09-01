<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Response;

class OpenFileResponse implements Response
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var bool
     */
    private $forceReload;

    private function __construct(string $path, int $offset = 0, bool $forceReload = false)
    {
        $this->path = $path;
        $this->offset = $offset;
        $this->forceReload = $forceReload;
    }

    public static function fromPath(string $path): OpenFileResponse
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
            'force_reload' => $this->forceReload
        ];
    }

    public function path(): string
    {
        return $this->path;
    }

    public function withForcedReload(bool $bool)
    {
        $new = clone $this;
        $new->forceReload = $bool;

        return $new;
    }
}
