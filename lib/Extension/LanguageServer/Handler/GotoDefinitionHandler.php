<?php

namespace Phpactor\Extension\LanguageServer\Handler;

use Generator;
use LanguageServerProtocol\CompletionItem;
use LanguageServerProtocol\CompletionList;
use LanguageServerProtocol\Location;
use LanguageServerProtocol\Position;
use LanguageServerProtocol\Range;
use LanguageServerProtocol\TextDocumentIdentifier;
use LanguageServerProtocol\TextDocumentItem;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\LanguageServer\Helper\OffsetHelper;
use Phpactor\Extension\WorseReflection\GotoDefinition\GotoDefinition;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

class GotoDefinitionHandler implements Handler
{
    /**
     * @var Manager
     */
    private $sessionManager;

    /**
     * @var SourceCodeReflector
     */
    private $reflector;

    /**
     * @var GotoDefinition
     */
    private $gotoDefinition;

    public function __construct(Manager $sessionManager, SourceCodeReflector $reflector)
    {
        $this->sessionManager = $sessionManager;
        $this->reflector = $reflector;
        $this->gotoDefinition = new GotoDefinition($reflector);
    }

    public function name(): string
    {
        return 'textDocument/definition';
    }

    public function __invoke(
        TextDocumentIdentifier $textDocument,
        Position $position,
        string $buftype = null,
        string $character = null,
        string $filename = null,
        string $gotoCmd = null,
        string $handle = null,
        string $languageId = null,
        string $line = null,
        string $method = null,
        array $text = null
    ): Generator
    {
        $textDocument = $this->sessionManager->current()->workspace()->get($textDocument->uri);

        $offset = $position->toOffset($textDocument->text);
        $offsetReflection = $this->reflector->reflectOffset(
            $textDocument->text,
            $offset
        );
        $result = $this->gotoDefinition->gotoDefinition($offsetReflection->symbolContext());

        $startPosition = OffsetHelper::offsetToPosition(
            file_get_contents($result->path()),
            $result->offset()
        );
        $endPosition = OffsetHelper::offsetToPosition(
            file_get_contents($result->path()),
            $result->offset()
        );

        $location = new Location('file://'.$result->path(), new Range(
            $startPosition, $endPosition
        ));

        yield $location;
    }
}
