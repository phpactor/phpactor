<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Integration\Adapter\Composer;

use Phpactor\Extension\ExtensionManager\Model\Extension;
use Phpactor\Extension\ExtensionManager\Service\InstallerService;
use Phpactor\Extension\ExtensionManager\Tests\Integration\IntegrationTestCase;
use RuntimeException;

class ComposerExtensionRepositoryTest extends IntegrationTestCase
{
    private $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadProject(
            'Extension',
            <<<'EOT'
                // File: composer.json
                {
                    "name": "test/extension",
                    "type": "phpactor-extension",
                    "extra": {
                        "phpactor.extension_class": "Foo"
                    },
                    "require": {
                        "test/library": "*"
                    }
                }
                EOT
        );
        $this->loadProject(
            'Library',
            <<<'EOT'
                // File: composer.json
                {
                    "name": "test/library"
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
                ],
                [
                    'type' => 'path',
                    'url' => $this->workspace->path('Library'),
                ]
            ]
        ]);
        $installer= $container->get('extension_manager.service.installer');
        $this->repository = $container->get('extension_manager.model.extension_repository');
        $installer->requireExtensions(['test/extension']);
    }

    public function testReturnsAllInstalledExtensions(): void
    {
        $extensions = $this->repository->installedExtensions();
        $this->assertCount(1, $extensions);
        $this->assertContainsOnlyInstancesOf(Extension::class, $extensions);
    }

    public function testHasPackage(): void
    {
        $this->assertTrue($this->repository->has('test/extension'));
        $this->assertFalse($this->repository->has('test/foobar'));
    }

    public function testThrowsExceptionWhenTryingToGetNonExistingRepository(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not find');
        $this->repository->find('not-existing-yeah');
    }
}
