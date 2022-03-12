<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

class FileToLint
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var string|null
     */
    private $contents;

    /**
     * @var int|null
     */
    private $version;

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
