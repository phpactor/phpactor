<?php

namespace Phpactor\Extension\ExtensionManager\Adapter\Composer;

use Composer\Package\AliasPackage;
use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\ComposerRepository;
use Composer\Repository\RepositoryInterface;
use Phpactor\Extension\ExtensionManager\Model\ExtensionRepository;
use Phpactor\Extension\ExtensionManager\Model\Extension;
use Phpactor\Extension\ExtensionManager\Model\ExtensionState;
use Phpactor\Extension\ExtensionManager\Model\Extensions;
use RuntimeException;

class ComposerExtensionRepository implements ExtensionRepository
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var RepositoryInterface
     */
    private $primaryRepository;

    /**
     * @var ComposerRepository
     */
    private $packagistRepository;

    /**
     * @var PackageExtensionFactory
     */
    private $extensionFactory;

    public function __construct(
        RepositoryInterface $repository,
        RepositoryInterface $primaryRepository,
        ComposerRepository $packagistRepository,
        PackageExtensionFactory $extensionFactory
    ) {
        $this->repository = $repository;
        $this->primaryRepository = $primaryRepository;
        $this->packagistRepository = $packagistRepository;
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function installedExtensions(): Extensions
    {
        return new Extensions(array_map(function (CompletePackageInterface $package) {
            return $this->extensionFactory->fromPackage(
                $package,
                $this->extensionState($package)
            );
        }, self::filter($this->repository->getPackages())));
    }

    /**
     * {@inheritDoc}
     */
    public function extensions(): Extensions
    {
        $packages = $this->packagistRepository->search('', 0, 'phpactor-extension');

        $allExtensions = new Extensions(array_map(function (array $packageInfo) {
            if (!$package = $this->repository->findPackage($packageInfo['name'], '*')) {
                $package = $this->packagistRepository->findPackage($packageInfo['name'], '*');
            }

            assert($package instanceof CompletePackageInterface);

            return $this->extensionFactory->fromPackage(
                $package,
                $this->extensionState($package)
            );
        }, $packages));

        return $this->installedExtensions()->merge($allExtensions);
    }

    public function find(string $extension): Extension
    {
        $package = $this->findPackage($extension);

        if (!$package) {
            throw new RuntimeException(sprintf(
                'Could not find package "%s"',
                $extension
            ));
        }

        if (!$package instanceof CompletePackageInterface) {
            throw new RuntimeException(sprintf(
                'Package must be a complete package, got "%s"',
                get_class($package)
            ));
        }

        return $this->extensionFactory->fromPackage($package, $this->extensionState($package));
    }

    public function has(string $extension): bool
    {
        return null !== $this->findPackage($extension);
    }

    /**
     * @param PackageInterface[] $packages
     * @return CompletePackageInterface[]
     */
    public static function filter(array $packages): array
    {
        return array_filter($packages, function (PackageInterface $package) {
            return
                $package->getType() === PackageExtensionFactory::PACKAGE_TYPE &&
                !$package instanceof AliasPackage &&
                $package instanceof CompletePackageInterface;
        });
    }

    private function belongsToPrimaryRepository(PackageInterface $package): bool
    {
        return null !== $this->primaryRepository->findPackage($package->getName(), '*');
    }

    private function findPackage(string $extension): ?PackageInterface
    {
        return $this->repository->findPackage($extension, '*');
    }

    private function extensionState(PackageInterface $package)
    {
        if ($this->belongsToPrimaryRepository($package)) {
            return ExtensionState::STATE_PRIMARY;
        }

        if ($this->has($package->getName())) {
            return ExtensionState::STATE_SECONDARY;
        }

        return ExtensionState::STATE_NOT_INSTALLED;
    }
}
