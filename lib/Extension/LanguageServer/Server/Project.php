<?php

namespace Phpactor\Extension\LanguageServer\Server;

use RuntimeException;

class Project
{
    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var array
     */
    private $capabilities;

    /**
     * @var string
     */
    private $processId;

    /**
     * @var bool
     */
    private $isInitialized = false;

    private $documents = [];

    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct()
    {
        $this->workspace = new Workspace();
    }

    public function initialize(string $rootPath, array $capabilities, string $processId = null)
    {
        if ($this->isInitialized) {
            throw new RuntimeException(sprintf(
                'Project has already been initialized, will not re-initialize'
            ));
        }

        $this->rootPath = $rootPath;
        $this->capabilities = $capabilities;
        $this->processId = $processId;
        $this->isInitialized = true;
    }

    public function workspace(): Workspace
    {
        return $this->workspace;
    }

    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    public function rootPath(): string
    {
        return $this->rootPath;
    }

    public function capabilities(): array
    {
        return $this->capabilities;
    }

    public function processId(): string
    {
        return $this->processId;
    }
}
