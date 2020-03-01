<?php

namespace Phpactor\Extension\Php\Model;

class ComposerPhpVersionResolver implements PhpVersionResolver
{
    /**
     * @var string
     */
    private $composerJsonPath;

    public function __construct(string $composerJsonPath)
    {
        $this->composerJsonPath = $composerJsonPath;
    }

    /**
     * {@inheritDoc}
     */
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
            return preg_replace('/[^0-9.]/', '', $json['require']['php']);
        }

        return null;
    }
}
