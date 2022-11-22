<?php

namespace Phpactor\Extension\LanguageServerCompletion\Util;

use Phpactor\LanguageServerProtocol\CompletionItemKind;
use Phpactor\Completion\Core\Suggestion;

class PhpactorToLspCompletionType
{
    public static function fromPhpactorType(?string $suggestionType): ?int
    {
        return match ($suggestionType) {
            Suggestion::TYPE_METHOD => CompletionItemKind::METHOD,
            Suggestion::TYPE_FUNCTION => CompletionItemKind::FUNCTION,
            Suggestion::TYPE_CONSTRUCTOR => CompletionItemKind::CONSTRUCTOR,
            Suggestion::TYPE_FIELD => CompletionItemKind::FIELD,
            Suggestion::TYPE_VARIABLE => CompletionItemKind::VARIABLE,
            Suggestion::TYPE_CLASS => CompletionItemKind::CLASS_,
            Suggestion::TYPE_INTERFACE => CompletionItemKind::INTERFACE,
            Suggestion::TYPE_MODULE => CompletionItemKind::MODULE,
            Suggestion::TYPE_PROPERTY => CompletionItemKind::PROPERTY,
            Suggestion::TYPE_UNIT => CompletionItemKind::UNIT,
            Suggestion::TYPE_VALUE => CompletionItemKind::VALUE,
            Suggestion::TYPE_ENUM => CompletionItemKind::ENUM,
            Suggestion::TYPE_KEYWORD => CompletionItemKind::KEYWORD,
            Suggestion::TYPE_SNIPPET => CompletionItemKind::KEYWORD,
            Suggestion::TYPE_COLOR => CompletionItemKind::COLOR,
            Suggestion::TYPE_FILE => CompletionItemKind::FILE,
            Suggestion::TYPE_REFERENCE => CompletionItemKind::REFERENCE,
            Suggestion::TYPE_CONSTANT => CompletionItemKind::CONSTANT,
            Suggestion::TYPE_FIELD => CompletionItemKind::FIELD,
            default => null,
        };
    }
}
