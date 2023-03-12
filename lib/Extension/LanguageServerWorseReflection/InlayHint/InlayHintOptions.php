<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\InlayHint;

class InlayHintOptions
{
    public function __construct(public bool $types, public bool $params)
    {
    }
}
