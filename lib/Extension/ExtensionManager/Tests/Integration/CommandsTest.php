<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Integration;

use Phpactor\Extension\Console\ConsoleExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandsTest extends IntegrationTestCase
{
    /**
     * @var Container
     */
    private $container;
    private $finder;

    public function setUp(): void
    {
        parent::setUp();
        $this->setupEnvironment();
    }

    public function testInstall(): void
    {
        [$exit, $out] = $this->runCommand([
            'command' => 'extension:install'
        ]);
        $this->assertEquals(0, $exit);
    }

    public function testRemove(): void
    {
        [$exit, $out] = $this->runCommand([
            'command' => 'extension:remove',
            'extension' =>  [ 'test/extension' ],
        ]);
        $this->assertEquals(0, $exit);
    }

    public function testList(): void
    {
        [$exit, $out] = $this->runCommand([
            'command' => 'extension:install',
            'extension' =>  'test/extension'
        ]);
        $this->assertEquals(0, $exit);

        [$exit, $out] = $this->runCommand([
            'command' => 'extension:list',
        ]);

        $this->assertStringContainsString('test/extension', $out);
        $this->assertEquals(0, $exit);
    }

    public function testUpdate(): void
    {
        [$exit, $out] = $this->runCommand([
            'command' => 'extension:update',
        ]);
        $this->assertEquals(0, $exit);
    }

    private function runCommand(array $params): array
    {
        $application = new Application();
        $application->setAutoExit(false);
        $application->setCommandLoader(
            $this->container->get(ConsoleExtension::SERVICE_COMMAND_LOADER)
        );
        $output = new BufferedOutput();
        $exit = $application->run(new ArrayInput($params), $output);

        return [$exit, $output->fetch()];
    }

    private function setupEnvironment(): void
    {
        $this->loadProject(
            'Extension',
            <<<'EOT'
                // File: composer.json
                {
                    "name": "test/extension",
                    "type": "phpactor-extension",
                    "extra": {
                        "phpactor.extension_class": "Foo"
                    }
                }
                EOT
        );

        $this->container = $this->container([
            'extension_manager.minimum_stability' => 'dev',
            'extension_manager.repositories' => [
                [
                    'type' => 'path',
                    'url' => $this->workspace->path('Extension'),
                ]
            ]
        ]);
        $installer = $this->container->get('extension_manager.service.installer');
        $installer->requireExtensions(['test/extension']);
        $this->finder = $this->container->get('extension_manager.adapter.composer.version_finder');
    }
}
