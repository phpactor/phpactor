<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\Line;
use Phpactor\CodeBuilder\Domain\Prototype\MethodBody;

class MethodBodyBuilder
{
    /**
     * @var Line[]
     */
    protected array $lines = [];

    public function __construct(private readonly MethodBuilder $parent)
    {
    }

    public function line(string $text): MethodBodyBuilder
    {
        $this->lines[] = Line::fromString($text);

        return $this;
    }

    public function build(): MethodBody
    {
        return MethodBody::fromLines($this->lines);
    }

    public function end(): MethodBuilder
    {
        return $this->parent;
    }
}
