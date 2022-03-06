<?php

namespace Phpactor\Extension\LanguageServerCompletion\Util;

use Phpactor\LanguageServerProtocol\CompletionItemKind;
use Phpactor\Completion\Core\Suggestion;

class PhpactorToLspCompletionType
{
    public static function fromPhpactorType(?string $suggestionType): ?int
    {
        switch ($suggestionType):
            case Suggestion::TYPE_METHOD:
                return CompletionItemKind::METHOD;
        case Suggestion::TYPE_FUNCTION:
                return CompletionItemKind::FUNCTION;
        case Suggestion::TYPE_CONSTRUCTOR:
                return CompletionItemKind::CONSTRUCTOR;
        case Suggestion::TYPE_FIELD:
                return CompletionItemKind::FIELD;
        case Suggestion::TYPE_VARIABLE:
                return CompletionItemKind::VARIABLE;
        case Suggestion::TYPE_CLASS:
                return CompletionItemKind::CLASS_;
        case Suggestion::TYPE_INTERFACE:
                return CompletionItemKind::INTERFACE;
        case Suggestion::TYPE_MODULE:
                return CompletionItemKind::MODULE;
        case Suggestion::TYPE_PROPERTY:
                return CompletionItemKind::PROPERTY;
        case Suggestion::TYPE_UNIT:
                return CompletionItemKind::UNIT;
        case Suggestion::TYPE_VALUE:
                return CompletionItemKind::VALUE;
        case Suggestion::TYPE_ENUM:
                return CompletionItemKind::ENUM;
        case Suggestion::TYPE_KEYWORD:
                return CompletionItemKind::KEYWORD;
        case Suggestion::TYPE_SNIPPET:
                return CompletionItemKind::KEYWORD;
        case Suggestion::TYPE_COLOR:
                return CompletionItemKind::COLOR;
        case Suggestion::TYPE_FILE:
                return CompletionItemKind::FILE;
        case Suggestion::TYPE_REFERENCE:
                return CompletionItemKind::REFERENCE;
        case Suggestion::TYPE_CONSTANT:
                return CompletionItemKind::CONSTANT;
        case Suggestion::TYPE_FIELD:
                return CompletionItemKind::FIELD;
        default:
                return null;
        endswitch;
    }
}
