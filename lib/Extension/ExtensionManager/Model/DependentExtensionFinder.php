<?php

namespace Phpactor\Extension\ExtensionManager\Model;

class DependentExtensionFinder
{
    private $repository;

    public function __construct(ExtensionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function findDependentExtensions(array $extensions): Extensions
    {
        $resolved = [];
        foreach ($extensions as $extension) {
            $this->repository->find($extension);
            $resolved = array_merge($this->findDependentPackages($extension), $resolved);
        }

        return new Extensions($resolved);
    }

    private function findDependentPackages(string $sourcePackage, array $dependents = []): array
    {
        foreach ($this->repository->installedExtensions() as $extension) {
            if (isset($dependents[$extension->name()])) {
                continue;
            }

            foreach ($extension->dependencies() as $dependency) {
                if ($dependency !== $sourcePackage) {
                    continue;
                }

                $dependents[$extension->name()] = $extension;
                $dependents = $this->findDependentPackages($extension->name(), $dependents);
            }
        }

        return $dependents;
    }
}
