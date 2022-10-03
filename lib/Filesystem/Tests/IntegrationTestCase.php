<?php

namespace Phpactor\Filesystem\Tests;

use Phpactor\TestUtils\Workspace;
use PHPUnit\Framework\TestCase;

class IntegrationTestCase extends TestCase
{
    protected function workspace(): Workspace
    {
        return new Workspace(__DIR__ . '/Workspace');
    }
}
