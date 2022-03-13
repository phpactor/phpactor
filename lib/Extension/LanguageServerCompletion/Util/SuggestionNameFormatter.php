<?php

namespace Phpactor\Extension\LanguageServerCompletion\Util;

use Phpactor\Completion\Core\Suggestion;

class SuggestionNameFormatter
{
    private bool $trimLeadingDollar;

    public function __construct(bool $trimLeadingDollar = false)
    {
        $this->trimLeadingDollar = $trimLeadingDollar;
    }

    public function format(Suggestion $suggestion): string
    {
        $name = $suggestion->name();

        switch ($suggestion->type()) {
            case Suggestion::TYPE_VARIABLE:
                return $this->trimLeadingDollar ? mb_substr($name, 1) : $name;
            case Suggestion::TYPE_FUNCTION:
            case Suggestion::TYPE_METHOD:
                return $name;
        }

        return $name;
    }
}
