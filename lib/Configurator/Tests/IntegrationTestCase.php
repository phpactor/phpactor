<?php

namespace Phpactor\Configurator\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;

class IntegrationTestCase extends TestCase
{
    public function workspace(): Workspace
    {
        return new Workspace(__DIR__ . '/Workspace');
    }
}
