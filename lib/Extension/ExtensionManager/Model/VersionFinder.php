<?php

namespace Phpactor\Extension\ExtensionManager\Model;

interface VersionFinder
{
    public function findBestVersion(string $extensionName): string;
}
