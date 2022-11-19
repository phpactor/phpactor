<?php

namespace Phpactor\Completion\Bridge\ObjectRenderer;

class ItemDocumentation
{
    public function __construct(private string $name, private string $docs, private object $object)
    {
    }

    public function docs(): string
    {
        return trim(strip_tags($this->docs));
    }

    public function name(): string
    {
        return $this->name;
    }

    public function object(): object
    {
        return $this->object;
    }
}
