<?php

namespace Phpactor\Extension\Php\Tests;

use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\TestUtils\Workspace;

class IntegrationTestCase extends TestCase
{
    public function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }
}
