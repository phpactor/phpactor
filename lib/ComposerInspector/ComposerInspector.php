<?php

namespace Phpactor\ComposerInspector;

final class ComposerInspector
{
    private const DEFAULT_BIN_DIR = 'vendor/bin';

    /**
     * @var array<string,Package>
     */
    private array $packages = [];

    private string $vendorBinDir = self::DEFAULT_BIN_DIR;

    private bool $loaded = false;

    public function __construct(
        private string $lockFile,
        private string $composerFile,
    ) {
    }

    public function package(string $name): ?Package
    {
        $this->readFiles();
        if (!isset($this->packages[$name])) {
            return null;
        }

        return $this->packages[$name];
    }

    public function binDir(): string
    {
        $this->readFiles();
        return $this->vendorBinDir;
    }

    private function readFiles(): void
    {
        if ($this->loaded) {
            return;
        }

        $lockContent = $this->parseFile($this->lockFile);
        foreach ($lockContent['packages'] ?? [] as $pkg) {
            $this->packages[(string)$pkg['name']] = $this->forVersion($pkg['name'], $pkg['version'], false);
        }
        foreach ($lockContent['packages-dev'] ?? [] as $pkg) {
            $this->packages[(string)$pkg['name']] = $this->forVersion($pkg['name'], $pkg['version'], true);
        }

        $composerContent = $this->parseFile($this->composerFile);
        $this->vendorBinDir = $composerContent['bin-dir'] ?? self::DEFAULT_BIN_DIR;

        $this->loaded = true;
    }

    /** @return array<string, mixed> */
    private function parseFile(string $fileName): array
    {
        $contents = file_get_contents($fileName);
        if (false === $contents) {
            return [];
        }

        $result = json_decode($contents, associative: true);

        if (!is_array($result)) {
            return [];
        }

        return $result;
    }

    private function forVersion(string $name, string $version, bool $isDev): Package
    {
        return new Package($name, $version, $isDev);
    }
}
