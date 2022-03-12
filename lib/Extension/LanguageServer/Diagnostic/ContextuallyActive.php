<?php

namespace Phpactor\Extension\LanguageServer\Diagnostic;

interface ContextuallyActive
{
    public function canBeActive(): bool;
}
