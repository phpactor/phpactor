<?php

namespace Phpactor\Extension\LanguageServerCompletion\Handler;

use Amp\CancellationToken;
use Amp\CancelledException;
use Amp\Delayed;
use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\NameImporter;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\NameImporterResult;
use Phpactor\LanguageServerProtocol\CompletionItem;
use Phpactor\LanguageServerProtocol\CompletionList;
use Phpactor\LanguageServerProtocol\CompletionOptions;
use Phpactor\LanguageServerProtocol\CompletionParams;
use Phpactor\LanguageServerProtocol\InsertTextFormat;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\SignatureHelpOptions;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\TextEdit;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Core\TypedCompletorRegistry;
use Phpactor\Extension\LanguageServerCompletion\Util\PhpactorToLspCompletionType;
use Phpactor\Extension\LanguageServerCompletion\Util\SuggestionNameFormatter;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;

class CompletionHandler implements Handler, CanRegisterCapabilities
{
    private TypedCompletorRegistry $registry;

    private bool $provideTextEdit;

    private SuggestionNameFormatter $suggestionNameFormatter;

    private Workspace $workspace;

    private bool $supportSnippets;

    private NameImporter $nameImporter;

    public function __construct(
        Workspace $workspace,
        TypedCompletorRegistry $registry,
        SuggestionNameFormatter $suggestionNameFormatter,
        NameImporter $nameImporter,
        bool $supportSnippets,
        bool $provideTextEdit = false
    ) {
        $this->registry = $registry;
        $this->provideTextEdit = $provideTextEdit;
        $this->workspace = $workspace;
        $this->suggestionNameFormatter = $suggestionNameFormatter;
        $this->nameImporter = $nameImporter;
        $this->supportSnippets = $supportSnippets;
    }

    public function methods(): array
    {
        return [
            'textDocument/completion' => 'completion',
        ];
    }

    public function completion(CompletionParams $params, CancellationToken $token): Promise
    {
        return \Amp\call(function () use ($params, $token) {
            $textDocument = $this->workspace->get($params->textDocument->uri);

            $languageId = $textDocument->languageId ?: 'php';
            $byteOffset = PositionConverter::positionToByteOffset($params->position, $textDocument->text);
            $suggestions = $this->registry->completorForType(
                $languageId
            )->complete(
                TextDocumentBuilder::create($textDocument->text)->language($languageId)->uri($textDocument->uri)->build(),
                $byteOffset
            );

            $items = [];
            $isIncomplete = false;
            foreach ($suggestions as $suggestion) {
                assert($suggestion instanceof Suggestion);

                $name = $this->suggestionNameFormatter->format($suggestion);
                $nameImporterResult = $this->importClassOrFunctionName($suggestion, $params);

                [$insertText, $insertTextFormat] = $this->determineInsertTextAndFormat(
                    $name,
                    $suggestion,
                    $nameImporterResult
                );

                $textEdits = $nameImporterResult->getTextEdits();

                $items[] = CompletionItem::fromArray([
                         'label' => $name,
                         'kind' => PhpactorToLspCompletionType::fromPhpactorType($suggestion->type()),
                         'detail' => $this->formatShortDescription($suggestion),
                         'documentation' => $suggestion->documentation(),
                         'insertText' => $insertText,
                         'sortText' => $this->sortText($suggestion),
                         'textEdit' => $this->textEdit($suggestion, $insertText, $textDocument),
                         'additionalTextEdits' => $textEdits,
                         'insertTextFormat' => $insertTextFormat
                     ]);

                try {
                    $token->throwIfRequested();
                } catch (CancelledException $cancellation) {
                    $isIncomplete = true;
                    break;
                }
                yield new Delayed(0);
            }

            $isIncomplete = $isIncomplete || !$suggestions->getReturn();

            return new CompletionList($isIncomplete, $items);
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->completionProvider = new CompletionOptions([':', '>', '$', '@']);
        $capabilities->signatureHelpProvider = new SignatureHelpOptions(['(', ',']);
    }

    /**
     * @return array{string,InsertTextFormat::*}
     */
    private function determineInsertTextAndFormat(
        string $name,
        Suggestion $suggestion,
        NameImporterResult $nameImporterResult
    ): array {
        $insertText = $name;
        $insertTextFormat = InsertTextFormat::PLAIN_TEXT;

        if ($this->supportSnippets) {
            $insertText = $suggestion->snippet() ?: $name;
            $insertTextFormat = $suggestion->snippet()
                ? InsertTextFormat::SNIPPET
                : InsertTextFormat::PLAIN_TEXT
            ;
        }

        if ($nameImporterResult->isSuccessAndHasAliasedNameImport()) {
            $alias = $nameImporterResult->getNameImport()->alias();
            $insertText = str_replace($name, $alias, $insertText);
        }

        return [$insertText, $insertTextFormat];
    }

    private function importClassOrFunctionName(
        Suggestion $suggestion,
        CompletionParams $params
    ): NameImporterResult {
        $suggestionNameImport = $suggestion->nameImport();

        if (!$suggestionNameImport) {
            return NameImporterResult::createEmptyResult();
        }

        $suggestionType = $suggestion->type();

        if (!in_array($suggestionType, [ 'class', 'function'])) {
            return NameImporterResult::createEmptyResult();
        }

        $textDocument = $this->workspace->get($params->textDocument->uri);
        $offset = PositionConverter::positionToByteOffset($params->position, $textDocument->text);

        return ($this->nameImporter)(
            $textDocument,
            $offset->toInt(),
            $suggestionType,
            $suggestionNameImport,
            false
        );
    }

    private function textEdit(
        Suggestion $suggestion,
        string $insertText,
        TextDocumentItem $textDocument
    ): ?TextEdit {
        if (false === $this->provideTextEdit) {
            return null;
        }

        $range = $suggestion->range();

        if (!$range) {
            return null;
        }
        return new TextEdit(
            new Range(
                PositionConverter::byteOffsetToPosition($range->start(), $textDocument->text),
                PositionConverter::byteOffsetToPosition($range->end(), $textDocument->text),
            ),
            $insertText
        );
    }

    private function formatShortDescription(Suggestion $suggestion): string
    {
        $prefix = '';
        if ($suggestion->classImport()) {
            $prefix = '↓ ';
        }

        return $prefix . $suggestion->shortDescription();
    }

    private function sortText(Suggestion $suggestion): ?string
    {
        if (null === $suggestion->priority()) {
            return null;
        }

        return sprintf('%04s-%s', $suggestion->priority(), $suggestion->name());
    }
}
