<?php

namespace Phpactor\Extension\ExtensionManager\Service;

use Exception;
use Phpactor\Extension\ExtensionManager\Model\DependentExtensionFinder;
use Phpactor\Extension\ExtensionManager\Model\Exception\CouldNotInstallExtension;
use Phpactor\Extension\ExtensionManager\Model\ExtensionConfigLoader;
use Phpactor\Extension\ExtensionManager\Model\ExtensionRepository;
use Phpactor\Extension\ExtensionManager\Model\Extensions;
use Phpactor\Extension\ExtensionManager\Model\Installer;
use Phpactor\Extension\ExtensionManager\Model\RemoveExtension;
use RuntimeException;

class RemoverService
{
    /**
     * @var Installer
     */
    private $installer;

    /**
     * @var DependentExtensionFinder
     */
    private $finder;

    /**
     * @var ExtensionRepository
     */
    private $repository;

    /**
     * @var ExtensionConfigLoader
     */
    private $configLoader;

    public function __construct(
        Installer $installer,
        DependentExtensionFinder $finder,
        ExtensionRepository $repository,
        ExtensionConfigLoader $configLoader
    ) {
        $this->installer = $installer;
        $this->finder = $finder;
        $this->repository = $repository;
        $this->configLoader = $configLoader;
    }

    public function findDependentExtensions(array $extensionNames): Extensions
    {
        return $this->finder->findDependentExtensions($extensionNames);
    }

    public function removeExtension(string $extensionName): void
    {
        $config = $this->configLoader->load();

        $extension = $this->repository->find($extensionName);

        if ($extension->state()->isPrimary()) {
            throw new RuntimeException(
                'Extension is a primary extension and cannot be removed'
            );
        }

        $config->unrequire($extensionName);
        $config->write();

        try {
            $this->installer->installForceUpdate();
        } catch (Exception $exception) {
            $config->revert();
            throw new CouldNotInstallExtension($exception->getMessage());
        }
    }
}
