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
use Phpactor\Extension\LanguageServerCompletion\Util\DocumentModifier;
use Phpactor\Extension\LanguageServerCompletion\Util\TextDocumentModifierResponse;
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

    /**
     * @param DocumentModifier[] $documentModifiers
     */
    public function __construct(
        private Workspace $workspace,
        private TypedCompletorRegistry $registry,
        private SuggestionNameFormatter $suggestionNameFormatter,
        private NameImporter $nameImporter,
        private bool $supportSnippets,
        private bool $provideTextEdit = false,
        private array $documentModifiers = []
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

            $modifiedDocumentText = $textDocument->text;
            $totalByteOffsetDifference = 0;

            // Allow documentModifiers to process the document. This will barely be usable for other extensions but
            // the Laravel blade one.
            /** @var TextDocumentModifierResponse[] $modifierResponses */
            $modifierResponses = [];
            foreach ($this->documentModifiers as $modifier) {
                if ($response = $modifier->process($modifiedDocumentText, $textDocument, $params->position)) {
                    $modifierResponses[] = $response;
                    // Update the modifiedDocumentText with the new body as it may have changed.
                    $modifiedDocumentText = $response->body;
                    // Update the totalByteOffsetDifference with the additional text as it may have changed.
                    $totalByteOffsetDifference += $response->additionalOffset;
                }
            }

            $languageId = $textDocument->languageId ?: 'php';

            // We can only allow one language override.
            if ($modifierResponses !== []) {
                $languageId = $modifierResponses[0]->language;
            }

            $byteOffset = PositionConverter::positionToByteOffset($params->position, $textDocument->text)
                ->add($totalByteOffsetDifference);

            $suggestions = $this->registry->completorForType(
                $languageId
            )->complete(
                TextDocumentBuilder::create($modifiedDocumentText)->language($languageId)->uri($textDocument->uri)->build(),
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
        return call(function () use ($request) {
            $item = CompletionItem::fromArray($request->params);
            $item = $this->resolve[$item->data]($item);
            return $item;
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
        $suggestionNameImport = $suggestion->fqn();

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
