<?php

namespace Phpactor\Filesystem\Domain;

use Phpactor\Filesystem\Domain\Exception\FilesystemNotFound;

class MappedFilesystemRegistry implements FilesystemRegistry
{
    /** @var array<string, Filesystem> */
    private array $filesystems = [];

    /** @param array<string, Filesystem> $filesystemMap */
    public function __construct(array $filesystemMap)
    {
        foreach ($filesystemMap as $name => $filesystem) {
            $this->add($name, $filesystem);
        }
    }

    public function get(string $name): Filesystem
    {
        if (!isset($this->filesystems[$name])) {
            throw new FilesystemNotFound(sprintf(
                'Unknown filesystem "%s", known filesystems "%s"',
                $name,
                implode('", "', array_keys($this->filesystems))
            ));
        }

        return $this->filesystems[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->filesystems[$name]);
    }

    /** @return array<string> */
    public function names(): array
    {
        return array_keys($this->filesystems);
    }

    private function add(string $name, Filesystem $filesystem): void
    {
        $this->filesystems[$name] = $filesystem;
    }
}
