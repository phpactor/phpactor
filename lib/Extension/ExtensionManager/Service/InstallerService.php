<?php

namespace Phpactor\Extension\ExtensionManager\Service;

use Exception;
use Phpactor\Extension\ExtensionManager\Model\Exception\CouldNotInstallExtension;
use Phpactor\Extension\ExtensionManager\Model\ExtensionConfig;
use Phpactor\Extension\ExtensionManager\Model\ExtensionConfigLoader;
use Phpactor\Extension\ExtensionManager\Model\ExtensionRepository;
use Phpactor\Extension\ExtensionManager\Model\Installer;
use Phpactor\Extension\ExtensionManager\Model\VersionFinder;

class InstallerService
{
    /**
     * @var Installer
     */
    private $installer;
    private $config;

    /**
     * @var VersionFinder
     */
    private $finder;

    /**
     * @var ExtensionRepository
     */
    private $extensionRepository;

    /**
     * @var ExtensionConfigLoader
     */
    private $configLoader;

    /**
     * @var ProgressLogger
     */
    private $progress;

    public function __construct(
        Installer $installer,
        ExtensionConfigLoader $configLoader,
        VersionFinder $finder,
        ExtensionRepository $extensionRepository,
        ProgressLogger $progress
    ) {
        $this->installer = $installer;
        $this->finder = $finder;
        $this->extensionRepository = $extensionRepository;
        $this->configLoader = $configLoader;
        $this->progress = $progress;
    }

    public function requireExtensions(array $extensions): void
    {
        $config = $this->configLoader->load();

        foreach ($extensions as $extension) {
            $version = $this->finder->findBestVersion($extension);
            $this->progress->log(sprintf('Using version "%s"', $version));
            $config->require($extension, $version);
        }

        $config->write();
        $this->installForceUpdate($config);
    }

    public function install(): void
    {
        $this->installer->install();
    }

    public function installForceUpdate(ExtensionConfig $config = null): void
    {
        if (!$config) {
            $this->installer->installForceUpdate();
            return;
        }

        try {
            $this->installer->installForceUpdate();
        } catch (Exception $couldNotInstall) {
            $config->revert();
            $this->progress->log('Rolling back configuration');
            throw new CouldNotInstallExtension($couldNotInstall->getMessage(), $couldNotInstall);
        }
    }
}
