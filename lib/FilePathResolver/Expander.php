<?php

namespace Phpactor\FilePathResolver;

interface Expander
{
    public function tokenName(): string;

    public function replacementValue(): string;
}
