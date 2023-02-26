<?php

namespace Phpactor\ComposerInspector;

use stdClass;
use RuntimeException;

final class ComposerInspector
{
    /**
     * @var array<string,Package>
     */
    private $packages = [];

    private bool $loaded = false;

    public function __construct(private string $path)
    {
    }

    public function package(string $name): ?Package
    {
        $this->readFile();
        if (!isset($this->packages[$name])) {
            return null;
        }

        return $this->packages[$name];
    }

    private function readFile(): void
    {
        if ($this->loaded) {
            return;
        }
        if (!file_exists($this->path)) {
            return;
        }

        $contents = file_get_contents($this->path);

        if (false === $contents) {
            throw new RuntimeException(sprintf(
                'Could not read file "%s"',
                $this->path
            ));
        }

        $obj = json_decode($contents);

        if (!$obj instanceof stdClass) {
            return;
        }

        foreach ($obj->{'packages'} ?? [] as $pkg) {
            $this->packages[$pkg->{'name'}] = $this->forVersion($pkg->{'name'}, $pkg->{'version'}, false);
        }
        foreach ($obj->{'packages-dev'} ?? [] as $pkg) {
            $this->packages[$pkg->{'name'}] = $this->forVersion($pkg->{'name'}, $pkg->{'version'}, true);
        }
    }

    private function forVersion(string $name, string $version, bool $isDev): Package
    {
        return new Package($name, $version, $isDev);
    }
}
