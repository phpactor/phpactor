<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model;

class FileToLint
{
    public function __construct(private string $uri, private ?string $contents = null, private ?int $version = null)
    {
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
