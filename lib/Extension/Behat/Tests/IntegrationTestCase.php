<?php

namespace Phpactor\Extension\Behat\Tests;

use Phpactor\TestUtils\Workspace;
use PHPUnit\Framework\TestCase;

class IntegrationTestCase extends TestCase
{
    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }
}
