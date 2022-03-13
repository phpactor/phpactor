<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\Line;
use Phpactor\CodeBuilder\Domain\Prototype\Lines;
use Phpactor\CodeBuilder\Domain\Prototype\MethodBody;

class MethodBodyBuilder
{
    /**
     * @var Lines[]
     */
    protected array $lines = [];

    private MethodBuilder $parent;

    public function __construct(MethodBuilder $parent)
    {
        $this->parent = $parent;
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
