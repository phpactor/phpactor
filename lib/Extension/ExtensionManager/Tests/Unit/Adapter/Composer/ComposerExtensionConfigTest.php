<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Unit\Adapter\Composer;

use Phpactor\Extension\ExtensionManager\Adapter\Composer\ComposerExtensionConfig;
use Phpactor\Extension\ExtensionManager\Tests\TestCase;
use RuntimeException;

class ComposerExtensionConfigTest extends TestCase
{
    const EXAMPLE_PATH = 'extension.json';

    /**
     * @var ComposerExtensionConfig
     */
    private $config;

    /**
     * @var string
     */
    private $path;

    public function setUp(): void
    {
        parent::setUp();
        $this->path = $this->workspace->path(self::EXAMPLE_PATH);
        file_put_contents($this->path, '{}');
        $this->config = new ComposerExtensionConfig(
            $this->path,
            'my-package',
            dirname($this->path) .  '/vendorext'
        );
    }

    public function testThrowsExceptionWithInvalidJson(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON');

        file_put_contents($this->path, 'asd');

        new ComposerExtensionConfig($this->path, 'one', 'two');
    }

    public function testRequires(): void
    {
        $this->config->require('foo', 'bar');
        $this->config->write();
        $this->assertArrayHasKey('require', $this->render());
        $this->assertEquals([
            'foo' => 'bar'
        ], $this->render()['require']);
    }

    public function testRevertsToOriginalConfig(): void
    {
        $this->config->require('foo', 'bar');
        $this->config->write();
        $this->config->revert();

        $this->assertTrue(array_intersect([], $this->render()) === []);
    }

    public function testAddsRepositories(): void
    {
        $repository = [
            'type' => 'hello',
            'url' => 'foo/bar'
        ];

        file_put_contents($this->path, json_encode([
            'repositories' => [ $repository ],
        ]));

        $this->config = new ComposerExtensionConfig(
            $this->path,
            'my-package',
            dirname($this->path) .  '/vendorext',
            'dev',
            [
                $repository,
            ]
        );
        $this->config->write();

        $this->assertEquals([
            $repository
        ], $this->render()['repositories']);
    }

    public function testUnrequireRemovesRequireElementCompletely(): void
    {
        $this->config->require('foo', 'bar');
        $this->config->revert();

        $this->config->unrequire('foo');
        $this->config->revert();

        $this->assertArrayNotHasKey('require', $this->render());
    }

    private function render(): array
    {
        return json_decode(file_get_contents($this->path), true);
    }
}
