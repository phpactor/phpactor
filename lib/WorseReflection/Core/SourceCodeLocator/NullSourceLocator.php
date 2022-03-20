<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator;

class NullSourceLocator implements SourceCodeLocator
{
    public function locate(Name $name): SourceCode
    {
        throw new SourceNotFound(sprintf(
            'Null locator won\'t find any source, tried to find "%s"',
            $name->__toString()
        ));
    }
}
