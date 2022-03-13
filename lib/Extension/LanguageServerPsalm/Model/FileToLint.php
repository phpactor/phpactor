<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model;

class FileToLint
{
    private string $uri;

    private ?string $contents;

    private ?int $version;

    public function __construct(string $uri, ?string $contents = null, ?int $version = null)
    {
        $this->uri = $uri;
        $this->contents = $contents;
        $this->version = $version;
    }

    public function version(): ?int
    {
        return $this->version;
    }

    public function contents(): ?string
    {
        return $this->contents;
    }

    public function uri(): string
    {
        return $this->uri;
    }
}
