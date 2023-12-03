<?php

namespace Phpactor\Extension\LanguageServerCompletion\Handler;

use Amp\CancellationToken;
use Amp\CancelledException;
use Amp\Delayed;
use Amp\Promise;
use Closure;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\NameImporter;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\NameImporterResult;
use Phpactor\LanguageServerProtocol\CompletionItem;
use Phpactor\LanguageServerProtocol\CompletionList;
use Phpactor\LanguageServerProtocol\CompletionOptions;
use Phpactor\LanguageServerProtocol\CompletionParams;
use Phpactor\LanguageServerProtocol\InsertTextFormat;
use Phpactor\LanguageServerProtocol\MarkupContent;
use Phpactor\LanguageServerProtocol\MarkupKind;
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
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;
use function Amp\call;

class CompletionHandler implements Handler, CanRegisterCapabilities
{
    /**
     * @var array<int,Closure(CompletionItem): CompletionItem>
     */
    private array $resolve = [];

    public function __construct(
        private Workspace $workspace,
        private TypedCompletorRegistry $registry,
        private SuggestionNameFormatter $suggestionNameFormatter,
        private NameImporter $nameImporter,
        private bool $supportSnippets,
        private bool $provideTextEdit = false
    ) {
    }

    public function methods(): array
    {
        return [
            'textDocument/completion' => 'completion',
            'completionItem/resolve' => 'resolveItem',
        ];
    }

    /**
     * @return Promise<array<CompletionItem>>
     */
    public function completion(CompletionParams $params, CancellationToken $token): Promise
    {
        return call(function () use ($params, $token) {
            $this->resolve = [];
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
            foreach ($suggestions as $index => $suggestion) {
                assert($suggestion instanceof Suggestion);

                $name = $this->suggestionNameFormatter->format($suggestion);
                $nameImporterResult = $this->importClassOrFunctionName($suggestion, $params);

                [$insertText, $insertTextFormat] = $this->determineInsertTextAndFormat(
                    $name,
                    $suggestion,
                    $nameImporterResult
                );

                $textEdits = $nameImporterResult->getTextEdits();

                $item = CompletionItem::fromArray([
                    'label' => $suggestion->label(),
                    'kind' => PhpactorToLspCompletionType::fromPhpactorType($suggestion->type()),
                    'insertText' => $insertText,
                    'sortText' => $this->sortText($suggestion),
                    'textEdit' => $this->textEdit($suggestion, $insertText, $textDocument),
                    'additionalTextEdits' => $textEdits,
                    'insertTextFormat' => $insertTextFormat,
                    'data' => $index,
                ]);

                $this->resolve[$index] = function (CompletionItem $item) use ($suggestion): CompletionItem {
                    $documentation = $suggestion->documentation();
                    $item->documentation = $documentation ? new MarkupContent(MarkupKind::MARKDOWN, $documentation) : null;
                    $item->detail = $this->formatShortDescription($suggestion);
                    return $item;
                };

                $items[] = $item;

                try {
                    $token->throwIfRequested();
                } catch (CancelledException) {
                    $this->resolve = [];
                    $isIncomplete = true;
                    break;
                }
                yield new Delayed(0);
            }


            $isIncomplete = $isIncomplete || !$suggestions->getReturn();

            return new CompletionList($isIncomplete, $items);
        });
    }

    /**
     * @return Promise<CompletionItem>
     */
    public function resolveItem(RequestMessage $request): Promise
    {
        /** @phpstan-ignore-next-line */
        return call(function () use ($request) {
            /** @phpstan-ignore-next-line */
            $item = CompletionItem::fromArray($request->params);

            if (!(is_string($item->data) || is_int($item->data)) || !array_key_exists($item->data, $this->resolve)) {
                return $item;
            }
            /** @phpstan-ignore-next-line - shouldn't happen but playing safe */
            if (null === $this->resolve[$item->data]) {
                return $item;
            }
            return $this->resolve[$item->data]($item);
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->completionProvider = new CompletionOptions([
            ':',
            '>',
            '$',
            '[',
            '@',
            '(',
            '\'',
            '"',
            '\\'
        ]);
        $capabilities->signatureHelpProvider = new SignatureHelpOptions(['(', ',']);
        $capabilities->completionProvider->resolveProvider = true;
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
        $suggestionNameImport = $suggestion->classImport();

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
