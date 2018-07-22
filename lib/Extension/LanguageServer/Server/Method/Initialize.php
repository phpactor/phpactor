<?php

namespace Phpactor\Extension\LanguageServer\Server\Method;

use Phpactor\Extension\LanguageServer\Protocol\CompletionOptions;
use Phpactor\Extension\LanguageServer\Protocol\InitializeResult;
use Phpactor\Extension\LanguageServer\Protocol\ServerCapabilities;
use Phpactor\Extension\LanguageServer\Server\Method;
use Phpactor\Extension\LanguageServer\Server\Project;

class Initialize implements Method
{
    /**
     * @var Project
     */
    private $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function name(): string
    {
        return 'initialize';
    }

    public function __invoke(
        array $capabilities = [],
        array $initializationOptions = [],
        string $processId = null,
        string $rootPath = null,
        string $rootUri = null,
        string $trace = null
    )
    {
        if (!$rootPath && $rootUri) {
            $rootPath = $rootUri;
        }
        $this->project->initialize($rootPath, $capabilities, $processId);

        $capabilities = new ServerCapabilities();
        $capabilities->completionProvider = new CompletionOptions();

        return new InitializeResult($capabilities);
    }
}
