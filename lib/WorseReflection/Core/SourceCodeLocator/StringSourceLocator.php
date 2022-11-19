<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\SourceCode;

class StringSourceLocator implements SourceCodeLocator
{
    public function __construct(private SourceCode $source)
    {
    }

    public function locate(Name $className): SourceCode
    {
        return $this->source;
    }
}
