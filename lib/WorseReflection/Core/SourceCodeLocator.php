<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Exception\SourceNotFound;

interface SourceCodeLocator
{
    /**
     * @throws SourceNotFound
     */
    public function locate(Name $name): SourceCode;
}
