<?php

namespace Phpactor\CodeTransform\Tests\Adapter\TolerantParser;

use Phpactor\CodeTransform\Tests\Adapter\AdapterTestCase;
use Phpactor\WorseReflection\Core\AstProvider;

class TolerantTestCase extends AdapterTestCase
{
    public function parser(): AstProvider
    {
        return new \Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider();
    }
}
