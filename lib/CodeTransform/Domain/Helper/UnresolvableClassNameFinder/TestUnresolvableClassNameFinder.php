<?php

namespace Phpactor\CodeTransform\Domain\Helper\UnresolvableClassNameFinder;

use Phpactor\CodeTransform\Domain\Helper\UnresolvableClassNameFinder;
use Phpactor\CodeTransform\Domain\NameWithByteOffsets;
use Phpactor\TextDocument\TextDocument;

class TestUnresolvableClassNameFinder implements UnresolvableClassNameFinder
{
    private NameWithByteOffsets $result;

    public function __construct(NameWithByteOffsets $result)
    {
        $this->result = $result;
    }

    public function find(TextDocument $sourceCode): NameWithByteOffsets
    {
        return $this->result;
    }
}
