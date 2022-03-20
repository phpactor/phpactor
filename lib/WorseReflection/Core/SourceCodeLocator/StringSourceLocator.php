<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\SourceCode;

class StringSourceLocator implements SourceCodeLocator
{
    private $source;

    public function __construct(SourceCode $source)
    {
        $this->source = $source;
    }

    public function locate(Name $className): SourceCode
    {
        return $this->source;
    }
}
