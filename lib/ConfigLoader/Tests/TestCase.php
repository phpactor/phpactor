<?php

namespace Phpactor\ConfigLoader\Tests;

use PHPUnit\Framework\TestCase as PhpunitTestCase;
use Phpactor\TestUtils\Workspace;

class TestCase extends PhpunitTestCase
{
    protected Workspace $workspace;

    public function setUp(): void
    {
        $this->workspace = Workspace::create(__DIR__  . '/Workspace');
        $this->workspace->reset();
    }
}
