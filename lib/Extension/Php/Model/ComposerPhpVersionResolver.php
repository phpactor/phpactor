<?php

namespace Phpactor\Extension\Php\Model;

class ComposerPhpVersionResolver implements PhpVersionResolver
{
    private string $composerJsonPath;

    public function __construct(string $composerJsonPath)
    {
        $this->composerJsonPath = $composerJsonPath;
    }

    
    public function resolve(): ?string
    {
        if (!file_exists($this->composerJsonPath)) {
            return null;
        }

        if (!$contents = file_get_contents($this->composerJsonPath)) {
            return null;
        }

        if (!$json = json_decode($contents, true)) {
            return null;
        }

        if (isset($json['config']['platform']['php'])) {
            return $json['config']['platform']['php'];
        }

        if (isset($json['require']['php'])) {
            return $this->resolveLowestVersion($json['require']['php']);
        }

        return null;
    }

    private function resolveLowestVersion(string $versionString): ?string
    {
        $versions = array_map(function (string $versionString) {
            return preg_replace('/[^0-9.]/', '', trim($versionString));
        }, (array)preg_split('{\|\|?}', $versionString));

        sort($versions);

        if (false === $version = reset($versions)) {
            return $versionString;
        }

        return $version;
    }
}
