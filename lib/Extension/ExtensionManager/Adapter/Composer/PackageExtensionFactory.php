<?php

namespace Phpactor\Extension\ExtensionManager\Adapter\Composer;

use Composer\Package\CompletePackageInterface;
use Composer\Package\Link;
use Phpactor\Extension\ExtensionManager\Adapter\Composer\Exception\InvalidExtensionPackage;
use Phpactor\Extension\ExtensionManager\Model\Extension;
use Phpactor\Extension\ExtensionManager\Model\ExtensionState;
use Phpactor\Extension\ExtensionManager\Model\Extensions;

class PackageExtensionFactory
{
    const PACKAGE_TYPE = 'phpactor-extension';
    const EXTRA_EXTENSION_CLASS = 'phpactor.extension_class';

    public function fromPackage(
        CompletePackageInterface $package,
        int $state = ExtensionState::STATE_NOT_INSTALLED
    ): Extension {
        $this->assertPackageType($package);
        $this->assertHasExtensionClass($package);

        return new Extension(
            $package->getName(),
            $package->getFullPrettyVersion(),
            (array)$package->getExtra()[self::EXTRA_EXTENSION_CLASS],
            $package->getDescription() ?: '',
            $this->extractDependencies($package),
            $state
        );
    }

    public function fromPackages(array $packages): Extensions
    {
        return new Extensions(array_map(function (CompletePackageInterface $package) {
            return $this->fromPackage($package);
        }, ComposerExtensionRepository::filter($packages)));
    }

    private function extractDependencies(CompletePackageInterface $package): array
    {
        $dependencies = array_map(function (Link $link) {
            return $link->getTarget();
        }, $package->getRequires());
        return $dependencies;
    }

    private function assertPackageType(CompletePackageInterface $package): void
    {
        if ($package->getType() === self::PACKAGE_TYPE) {
            return;
        }

        throw new InvalidExtensionPackage(sprintf(
            'Package "%s" has type "%s", but to be an extension it needs to be "%s"',
            $package->getName(),
            $package->getType(),
            self::PACKAGE_TYPE
        ));
    }

    private function assertHasExtensionClass(CompletePackageInterface $package): void
    {
        $extra = $package->getExtra();

        if (isset($extra[self::EXTRA_EXTENSION_CLASS])) {
            return;
        }

        throw new InvalidExtensionPackage(sprintf(
            'Package "%s" does not have the "%s" extra configuration key. This should provide the FQN to the extension class',
            $package->getName(),
            self::EXTRA_EXTENSION_CLASS
        ));
    }
}
