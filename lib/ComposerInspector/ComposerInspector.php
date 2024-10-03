<?php

namespace Phpactor\ComposerInspector;

use stdClass;
use RuntimeException;

final class ComposerInspector
{
    private const DEFAULT_BIN_DIR = 'vendor/bin';

    /**
     * @var array<string,Package>
     */
    private array $packages = [];

    private string $binDir = self::DEFAULT_BIN_DIR;

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

    public function getBinDir(): string
    {
        $this->readFiles();
        return $this->binDir;
    }

    private function readFiles(): void
    {
        if ($this->loaded || !file_exists($this->lockFile) || !file_exists($this->composerFile)) {
            $this->loaded = true;
            return;
        }

        $lockContent = $this->parseFile($this->lockFile);
        foreach ($lockContent->{'packages'} ?? [] as $pkg) {
            $this->packages[(string)$pkg->{'name'}] = $this->forVersion($pkg->{'name'}, $pkg->{'version'}, false);
        }
        foreach ($lockContent->{'packages-dev'} ?? [] as $pkg) {
            $this->packages[(string)$pkg->{'name'}] = $this->forVersion($pkg->{'name'}, $pkg->{'version'}, true);
        }

        $composerContent = $this->parseFile($this->composerFile);
        $this->binDir = $composerContent->{'bin-dir'} ?? self::DEFAULT_BIN_DIR;
    }

    private function parseFile(string $fileName): ?stdClass
    {
        $contents = file_get_contents($fileName);

        if (false === $contents) {
            throw new RuntimeException(sprintf('Could not read file "%s"', $this->lockFile));
        }

        $obj = json_decode($contents);

        if (!$obj instanceof stdClass) {
            return null;
        }

        return $obj;
    }

    private function forVersion(string $name, string $version, bool $isDev): Package
    {
        return new Package($name, $version, $isDev);
    }
}
