<?php

namespace Phpactor\Extension\LanguageServerPsalm\Tests;

use Phpactor\TestUtils\Workspace;
use PHPUnit\Framework\TestCase;

abstract class IntegrationTestCase extends TestCase
{
    protected function tearDown(): void
    {
//        $this->workspace()->reset();
    }

    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }
}
