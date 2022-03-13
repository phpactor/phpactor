<?php

namespace Phpactor\Filesystem\Domain;

class FallbackFilesystemRegistry implements FilesystemRegistry
{
    private FilesystemRegistry $registry;

    private string $fallback;

    public function __construct(FilesystemRegistry $registry, string $fallback)
    {
        $this->registry = $registry;
        $this->fallback = $fallback;
    }

    public function get(string $name): Filesystem
    {
        if (false === $this->registry->has($name)) {
            return $this->registry->get($this->fallback);
        }

        return $this->registry->get($name);
    }

    public function has(string $name)
    {
        return $this->registry->has($name);
    }

    public function names(): array
    {
        return $this->registry->names();
    }
}
