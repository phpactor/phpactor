<?php

namespace Phpactor\Completion\Core;

use Closure;

interface SuggestionDocumentor
{
    public function document(Suggestion $suggestion): Closure;
}
