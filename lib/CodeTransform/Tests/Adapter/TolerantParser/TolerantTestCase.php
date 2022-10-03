<?php

namespace Phpactor\CodeTransform\Tests\Adapter\TolerantParser;

use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Tests\Adapter\AdapterTestCase;

class TolerantTestCase extends AdapterTestCase
{
    public function parser(): Parser
    {
        return new Parser();
    }
}
