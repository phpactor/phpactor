<?php

namespace Phpactor\WorseReflection\Core\Inference;

use RuntimeException;

final class FrameStack
{
    private Frame $current;

    /**
     * @var Frame[]
     */
    private array $frames;

    private function __construct(Frame $initial)
    {
        $this->current = $initial;
        $this->frames = [];
    }

    public static function new(): self
    {
        return new self(new Frame());
    }

    public function current(): Frame
    {
        return $this->current;
    }

    public function newFrame(): self
    {
        $this->frames[] = $this->current;
        $this->current = new Frame();

        return $this;
    }

    public function popFrame(): void
    {
        $current = array_pop($this->frames);
        if (null === $current) {
            throw new RuntimeException(
                'Cannot pop frame because there are no frames to pop from'
            );
        }
        $this->current = $current;
    }
}
