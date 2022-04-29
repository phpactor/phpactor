<?php

namespace Phpactor\Extension\Rpc\Response;

use Phpactor\Extension\Rpc\Response;
use RuntimeException;

class OpenFileResponse implements Response
{
    const TARGET_FOCUSED_WINDOW = 'focused_window';
    const TARGET_VERTICAL_SPLIT = 'vsplit';
    const TARGET_HORIZONTAL_SPLIT = 'hsplit';
    const TARGET_NEW_TAB = 'new_tab';
    const VALID_TARGETS = [
        self::TARGET_FOCUSED_WINDOW,
        self::TARGET_VERTICAL_SPLIT,
        self::TARGET_HORIZONTAL_SPLIT,
        self::TARGET_NEW_TAB
    ];

    private string $path;

    private int $offset;

    private bool $forceReload;

    private string $target;

    private function __construct(string $path, int $offset = 0, bool $forceReload = false, string $target = self::TARGET_FOCUSED_WINDOW)
    {
        $this->path = $path;
        $this->offset = $offset;
        $this->forceReload = $forceReload;
        $this->target = $target;
    }

    public static function fromPath(string $path): OpenFileResponse
    {
        return new self($path);
    }

    public static function fromPathAndOffset(string $path, int $offset): self
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
            'force_reload' => $this->forceReload,
            'target' => $this->target,
        ];
    }

    public function path(): string
    {
        return $this->path;
    }

    public function target(): string
    {
        return $this->target;
    }

    public function withForcedReload(bool $bool): OpenFileResponse
    {
        $new = clone $this;
        $new->forceReload = $bool;

        return $new;
    }

    public function withTarget(string $target): OpenFileResponse
    {
        if (!in_array($target, self::VALID_TARGETS)) {
            throw new RuntimeException(sprintf(
                'Unknown target "%s", known targets "%s"',
                $target,
                implode('", "', self::VALID_TARGETS)
            ));
        }
        $new = clone $this;
        $new->target = $target;

        return $new;
    }
}
