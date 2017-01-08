<?php

namespace Phpactor\Generation;

use Phpactor\CodeContext;

interface SnippetGeneratorInterface
{
    public function generate(CodeContext $codeContext): string;
}
