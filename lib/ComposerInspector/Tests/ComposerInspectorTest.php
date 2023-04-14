<?php

namespace Phpactor\ComposerInspector\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\ComposerInspector\ComposerInspector;
use Phpactor\TestUtils\Workspace;

class ComposerInspectorTest extends TestCase
{
    private Workspace $workspace;

    public function setUp(): void
    {
        $this->workspace = new Workspace(__DIR__ . '/Workspace');
    }

    public function testReturnsPackage(): void
    {
        $this->putComposer('{"packages":[{"name":"phpstan/phpstan", "version": "^1.0"}]}');
        $package = $this->inspector()->package('phpstan/phpstan');
        self::assertNotNull($package);
        self::assertEquals('phpstan/phpstan', $package->name);
        self::assertEquals('^1.0', $package->version);
        self::assertFalse($package->isDev);
    }

    public function testReturnsDevPackage(): void
    {
        $this->putComposer('{"packages-dev":[{"name":"phpstan/phpstan", "version": "^1.0"}]}');
        $package = $this->inspector()->package('phpstan/phpstan');
        self::assertNotNull($package);
        self::assertEquals('phpstan/phpstan', $package->name);
        self::assertTrue($package->isDev);
    }

    private function inspector(): ComposerInspector
    {
        return (new ComposerInspector($this->workspace->path('composer.lock')));
    }

    private function putComposer(string $file): void
    {
        $this->workspace->put('composer.lock', $file);
    }
}
