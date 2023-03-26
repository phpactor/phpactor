<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\TextDocument\TextDocument;

class StringSourceLocator implements SourceCodeLocator
{
    public function __construct(private TextDocument $source)
    {
    }

    public function locate(Name $className): TextDocument
    {
        return $this->source;
    }
}
