<?php

namespace Phpactor\ComposerInspector;

use stdClass;
use RuntimeException;

final class ComposerInspector
{
    public function __construct(private string $path)
    {
    }

    public function package(string $name): ?Package
    {
        $composer = $this->readFile();
    }

    private function readFile(): stdClass
    {
        if (!file_exists($this->path)) {
            throw new RuntimeException(sprintf(
                'File "%s" does not exist',
                $this->path
            ));
        }

        $contents = file_get_contents($this->path);

        if (false === $contents) {
            throw new RuntimeException(sprintf(
                'Could not read file "%s"',
                $this->path
            ));
        }

        $obj = json_decode($contents);

        return $obj;
    }
}
