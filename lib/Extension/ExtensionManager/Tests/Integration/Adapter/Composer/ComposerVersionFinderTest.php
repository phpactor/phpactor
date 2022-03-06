<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Integration\Adapter\Composer;

use Phpactor\Extension\ExtensionManager\Model\VersionFinder;
use Phpactor\Extension\ExtensionManager\Tests\Integration\IntegrationTestCase;
use RuntimeException;

class ComposerVersionFinderTest extends IntegrationTestCase
{
    /**
     * @var VersionFinder
     */
    private $finder;

    public function setUp(): void
    {
        parent::setUp();
        $this->setupEnvironment();
    }

    public function testFindsLatestVersion(): void
    {
        $version = $this->finder->findBestVersion('test/extension');

        $this->assertEquals('dev-master', $version);
    }

    public function testThrowsExceptionIfNoCandidatesFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not find suitable version for extension "test/no"');

        $this->finder->findBestVersion('test/no');
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

        /** @var InstallerService $installer */
        $container = $this->container([
            'extension_manager.minimum_stability' => 'dev',
            'extension_manager.repositories' => [
                [
                    'type' => 'path',
                    'url' => $this->workspace->path('Extension'),
                ]
            ]
        ]);
        $installer = $container->get('extension_manager.service.installer');
        $installer->requireExtensions(['test/extension']);
        $this->finder = $container->get('extension_manager.adapter.composer.version_finder');
    }
}
