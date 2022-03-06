<?php

namespace Phpactor\Filesystem\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;

class IntegrationTestCase extends TestCase
{
    protected function workspace(): Workspace
    {
        return new Workspace(__DIR__ . '/Workspace');
    }
}
