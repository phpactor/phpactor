<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class MethodBody extends Prototype
{
    public function __construct(private ?Lines $lines = null)
    {
        parent::__construct();
    }

    /**
     * @param array<Line> $lines
     */
    public static function fromLines(array $lines): MethodBody
    {
        return new self(Lines::fromLines($lines));
    }

    public static function empty(): MethodBody
    {
        return new self(Lines::empty());
    }

    public static function none(): self
    {
        return new self();
    }

    public function lines(): ?Lines
    {
        return $this->lines;
    }
}
