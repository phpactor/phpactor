<?php

namespace Phpactor\Extension\ExtensionManager\Adapter\Composer;

use Composer\Installer as ComposerInstaller;
use Phpactor\Container\Container;
use Phpactor\Extension\ExtensionManager\Model\Exception\CouldNotInstallExtension;
use Phpactor\Extension\ExtensionManager\Model\Installer;

class LazyComposerInstaller implements Installer
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function install(): void
    {
        $this->runInstall($this->installer());
    }

    public function installForceUpdate(): void
    {
        $installer = $this->installer();
        $installer->setUpdate(true);
        $this->runInstall($installer);
    }

    private function installer(): ComposerInstaller
    {
        $installer = $this->container->get('extension_manager.installer');
        return $installer;
    }

    private function runInstall(ComposerInstaller $installer): void
    {
        $installer->setDevMode(false);

        $status = $installer->run();

        if ($status === 0) {
            return;
        }

        throw new CouldNotInstallExtension(sprintf(
            'Composer exited with "%s"',
            $status
        ));
    }
}
