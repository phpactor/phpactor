<?php

namespace Phpactor\Extension\LanguageServerCompletion\Util;

use Phpactor\Completion\Core\Suggestion;

class SuggestionNameFormatter
{
    public function __construct(private readonly bool $trimLeadingDollar = false)
    {
    }

    public function format(Suggestion $suggestion): string
    {
        $name = $suggestion->name();
        return match ($suggestion->type()) {
            Suggestion::TYPE_VARIABLE => $this->trimLeadingDollar ? mb_substr($name, 1) : $name,
            Suggestion::TYPE_FUNCTION, Suggestion::TYPE_METHOD => $name,
            default => $name,
        };
    }
}
