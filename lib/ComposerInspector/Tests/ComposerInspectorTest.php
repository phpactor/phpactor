<?php

namespace Phpactor\ComposerInspector\Tests;

use Generator;
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
        $this->putComposerLock('{"packages":[{"name":"phpstan/phpstan", "version": "^1.0"}]}');
        $package = $this->inspector()->package('phpstan/phpstan');
        self::assertNotNull($package);
        self::assertEquals('phpstan/phpstan', $package->name);
        self::assertEquals('^1.0', $package->version);
        self::assertFalse($package->isDev);
    }

    public function testReturnsDevPackage(): void
    {
        $this->putComposerLock('{"packages-dev":[{"name":"phpstan/phpstan", "version": "^1.0"}]}');
        $package = $this->inspector()->package('phpstan/phpstan');
        self::assertNotNull($package);
        self::assertEquals('phpstan/phpstan', $package->name);
        self::assertTrue($package->isDev);
    }

    /** @dataProvider provideReturnsBinDirectory */
    public function testReturnsBinDirectory(string $composerContent, string $binPath): void
    {
        $this->putComposerLock('{"packages-dev":[{"name":"phpstan/phpstan", "version": "^1.0"}]}');
        $this->putComposer($composerContent);

        self::assertSame($binPath, $this->inspector()->binDir());
    }

    /**
    * @return Generator<array{string, string}>
    */
    public function provideReturnsBinDirectory(): Generator
    {
        yield 'no bin directory' => ['', 'vendor/bin'];
        yield 'bin directory specified' => ['{"bin-dir": "bin"}', 'bin'];
    }

    private function inspector(): ComposerInspector
    {
        return (new ComposerInspector(
            $this->workspace->path('composer.lock'),
            $this->workspace->path('composer.json')
        ));
    }

    private function putComposer(string $contents): void
    {
        $this->workspace->put('composer.json', $contents);
    }

    private function putComposerLock(string $contents): void
    {
        $this->workspace->put('composer.lock', $contents);
    }
}
