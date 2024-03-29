<?php

namespace Phpactor\Filesystem\Domain;

interface FilesystemRegistry
{
    public function get(string $name): Filesystem;

    public function has(string $name): bool;

    /**
     * @return array<string>
     */
    public function names(): array;
}
