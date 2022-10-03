<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests;

use Phpactor\TestUtils\Workspace;
use PHPUnit\Framework\TestCase;

abstract class IntegrationTestCase extends TestCase
{
    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }
}
