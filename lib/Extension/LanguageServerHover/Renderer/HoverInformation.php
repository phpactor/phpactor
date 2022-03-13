<?php

namespace Phpactor\Extension\LanguageServerHover\Renderer;

class HoverInformation
{
    private string $name;

    private string $docs;

    private object $object;

    public function __construct(string $name, string $docs, object $object)
    {
        $this->name = $name;
        $this->docs = $docs;
        $this->object = $object;
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
