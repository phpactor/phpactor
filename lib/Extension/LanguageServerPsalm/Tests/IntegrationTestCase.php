<?php

namespace Phpactor\Extension\LanguageServerPsalm\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;

abstract class IntegrationTestCase extends TestCase
{
    protected function tearDown(): void
    {
        //        $this->workspace()->reset();
    }

    protected static function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }
}
