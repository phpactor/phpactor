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

    public function testInspectComposer(): void
    {
        $this->putComposer('{"require":{"phpstan/phpstan":"^1.0"}}');
        $package = $this->inspector()->package('phpstan/phpstan');
        self::assertNotNull($package);
        self::assertEquals('phpstan/phpstan', $package->name);
    }

    private function inspector(): ComposerInspector
    {
        return (new ComposerInspector($this->workspace->path('composer.json')));
    }

    private function putComposer(string $file)
    {
        $this->workspace->put('composer.json', $file);
    }
}
