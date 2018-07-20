<?php

namespace Phpactor\Extension\LanguageServer\Protocol;

class ServerCapabilities
{
	/**
	 * Defines how text documents are synced. Is either a detailed structure defining each notification or
	 * for backwards compatibility the TextDocumentSyncKind number. If omitted it defaults to `TextDocumentSyncKind.None`.
     *
     * @var TextDocumentSyncOptions|int
     *
	 */
    public $textDocumentSync = null;

	/**
	 * The server provides hover support.
     *
     * @var boolean
	 */
    public $hoverProvider = false;

	/**
	 * The server provides completion support.
     *
     * @var CompletionOptions
	 */
    public $completionProvider = null;

	/**
	 * The server provides signature help support.
     *
     * @var SignatureHelpOptions
	 */
    public $signatureHelpProvider = null;

	/**
	 * The server provides goto definition support.
     *
     * @var bool
	 */
    public $definitionProvider = false;

	/**
	 * The server provides Goto Type Definition support.
	 *
	 * Since 3.6.0
     *
     * @var bool
	 */
    public $typeDefinitionProvider = false;

	/**
	 * The server provides Goto Implementation support.
	 *
	 * Since 3.6.0
     *
     * @var bool
	 */
    public $implementationProvider = false;

	/**
	 * The server provides find references support.
     *
     * @var bool
	 */
    public $referencesProvider = false;

	/**
	 * The server provides document highlight support.
     *
     * @var bool
	 */
    public $documentHighlightProvider = false;

	/**
	 * The server provides document symbol support.
     *
     * @var bool
	 */
    public $documentSymbolProvider = false;

	/**
	 * The server provides workspace symbol support.
     *
     * @var bool
	 */
    public $workspaceSymbolProvider = false;

	/**
     * The server provides code actions.
     *
     * @var bool
	 */
    public $codeActionProvider = false;

	/**
	 * The server provides code lens.
     *
     * @var CodeLensOptions|null
	 */
    public $codeLensProvider = null;

	/**
	 * The server provides document formatting.
     *
     * @var bool
	 */
    public $documentFormattingProvider = false;

	/**
	 * The server provides document range formatting.
     *
     * @var bool
	 */
    public $documentRangeFormattingProvider = null;

	/**
	 * The server provides document formatting on typing.
     *
     * @var DocumentOnTypeFormattingOptions|null
	 */
    public $documentOnTypeFormattingProvider = null;

	/**
	 * The server provides rename support.
     *
     * @var bool
	 */
    public $renameProvider = false;

	/**
	 * The server provides document link support.
     *
     * @var DocumentLinkOptions
	 */
    public $documentLinkProvider = null;

	/**
	 * The server provides color provider support.
	 *
     * Since 3.6.0
     *
     * @var bool
     *
	 */
    public $colorProvider = false;

	/**
	 * The server provides execute command support.
     *
     * @var ExecuteCommandOptions|null
	 */
    public $executeCommandProvider = null;

	/**
	 * Workspace specific server capabilities
	 */
    public $workspace = null;

    /**
	 * Experimental server capabilities.
	 */
	public $experimental = null;
}
