<?php

namespace Phpactor\Extension\LanguageServer\Protocol;

class InitializeParams
{
    /**
     * The process Id of the parent process that started
     * the server. Is null if the process has not been started by another process.
     * If the parent process is not alive then the server should exit (see exit notification) its process.
     *
     * @var int
     */
    public $processId;

    /*
     * The rootPath of the workspace. Is null
     * if no folder is open.
     *
     * @var string
     */
    public $rootPath;

    /**
     * The rootUri of the workspace. Is null if no folder is open. If both
     * `rootPath` and `rootUri` are set `rootUri` wins.
     *
     * @var string
     */
    public $rootUri;

    /**
     * User provided initialization options.
     *
     * @var array
     */
    public $initializationOptions = [];

    /**
     * The capabilities provided by the client (editor or tool)
     * @var array
     */
    public $capabilities = [];

    /**
     * The initial trace setting. If omitted trace is disabled ('off').
     * @var string
     */
    public $trace = 'off';

    /**
     * The workspace folders configured in the client when the server starts.
     * This property is only available if the client supports workspace folders.
     * It can be `null` if the client supports workspace folders but none are
     * configured.
     *
     * @since 3.6.0
     *
     * @var array
     */
    public $workspaceFolders;

    public function __construct(
        int $processId = null,
        string $rootPath = null,
        string $rootUri = null,
        array $initializationOptions = [],
        array $capabilities = [],
        string $trace = null,
        array $workspaceFolders = []
    ) {
        $this->rootPath = $rootPath;
        $this->rootUri = $rootUri;
        $this->initializationOptions = $initializationOptions;
        $this->capabilities = $capabilities;
        $this->trace = $trace;
        $this->workspaceFolders = $workspaceFolders;
    }
}
