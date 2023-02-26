<?php

namespace Phpactor\ComposerInspector;

use stdClass;
use RuntimeException;
use function Amp\Dns\isValidName;

final class ComposerInspector
{
    public function __construct(private string $path)
    {
    }

    public function package(string $name): ?Package
    {
        $composer = $this->readFile();
        if (isset($composer->{'require'}->{$name})) {
            $version = $composer->{'require'}->{$name};
            return $this->forVersion($name, $version, false);
        }
        if (isset($composer->{'require-dev'}->{$name})) {
            $version = $composer->{'require-dev'}->{$name};
            return $this->forVersion($name, $version, true);
        }

        return null;
    }

    private function readFile(): stdClass
    {
        if (!file_exists($this->path)) {
            return new stdClass();
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
            return new stdClass();
        }

        return $obj;
    }

    private function forVersion(string $name, string $version, bool $isDev): Package
    {
        return new Package($name, $version, $isDev);
    }
}
