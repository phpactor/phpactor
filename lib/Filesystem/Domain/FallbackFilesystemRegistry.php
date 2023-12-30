<?php

namespace Phpactor\Filesystem\Domain;

class FallbackFilesystemRegistry implements FilesystemRegistry
{
    public function __construct(private FilesystemRegistry $registry, private string $fallback)
    {
    }

    public function get(string $name): Filesystem
    {
        if (false === $this->registry->has($name)) {
            return $this->registry->get($this->fallback);
        }

        return $this->registry->get($name);
    }

    public function has(string $name): bool
    {
        return $this->registry->has($name);
    }

    public function names(): array
    {
        return $this->registry->names();
    }
}
