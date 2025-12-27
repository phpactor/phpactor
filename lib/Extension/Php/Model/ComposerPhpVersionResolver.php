<?php

namespace Phpactor\Extension\Php\Model;

class ComposerPhpVersionResolver implements PhpVersionResolver
{
    public function __construct(private readonly string $composerJsonPath)
    {
    }


    public function resolve(): ?string
    {
        if (!file_exists($this->composerJsonPath)) {
            return null;
        }

        if (!$contents = file_get_contents($this->composerJsonPath)) {
            return null;
        }

        $json = json_decode($contents, true);
        if (!$json || !is_array($json)) {
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

    public function name(): string
    {
        return 'composer';
    }

    private function resolveLowestVersion(string $versionString): ?string
    {
        /** @phpstan-ignore-next-line */
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
