<?php

namespace Phpactor\WorseReflection\Core;

final class NodeText
{
    private $nodeText;

    private function __construct($nodeText)
    {
        $this->nodeText = $nodeText;
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
