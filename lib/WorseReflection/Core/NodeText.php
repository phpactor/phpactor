<?php

namespace Phpactor\WorseReflection\Core;

final class NodeText
{
    private function __construct(private $nodeText)
    {
    }

    public function __toString()
    {
        return $this->nodeText;
    }

    public static function fromString(string $nodeText): NodeText
    {
        return new self($nodeText);
    }
}
