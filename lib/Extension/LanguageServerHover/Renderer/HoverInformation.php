<?php

namespace Phpactor\Extension\LanguageServerHover\Renderer;

class HoverInformation
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $docs;
    /**
     * @var object
     */
    private $object;

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
