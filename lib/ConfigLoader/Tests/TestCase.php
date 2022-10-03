<?php

namespace Phpactor\ConfigLoader\Tests;

use Phpactor\TestUtils\Workspace;
use PHPUnit\Framework\TestCase as PhpunitTestCase;

class TestCase extends PhpunitTestCase
{
    protected Workspace $workspace;

    public function setUp(): void
    {
        $this->workspace = Workspace::create(__DIR__  . '/Workspace');
        $this->workspace->reset();
    }
}
