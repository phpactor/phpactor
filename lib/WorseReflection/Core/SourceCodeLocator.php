<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;

interface SourceCodeLocator
{
    /**
     * @throws SourceNotFound
     */
    public function locate(Name $name): TextDocument;
}
