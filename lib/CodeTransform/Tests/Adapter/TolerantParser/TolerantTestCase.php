<?php

namespace Phpactor\CodeTransform\Tests\Adapter\TolerantParser;

use Phpactor\CodeTransform\Tests\Adapter\AdapterTestCase;
use Microsoft\PhpParser\Parser;

class TolerantTestCase extends AdapterTestCase
{
    public function parser(): Parser
    {
        return new Parser();
    }
}
