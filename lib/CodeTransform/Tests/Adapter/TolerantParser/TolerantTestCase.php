<?php

namespace Phpactor\CodeTransform\Tests\Adapter\TolerantParser;

use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\CodeTransform\Tests\Adapter\AdapterTestCase;

class TolerantTestCase extends AdapterTestCase
{
    public function parser(): AstProvider
    {
        return new TolerantAstProvider();
    }
}
