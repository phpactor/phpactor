<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Formatter;

use Amp\Promise;
use Phpactor\TextDocument\TextEdits;
use Phpactor\TextDocument\TextDocument;

interface Formatter
{
    /**
     * @return Promise<TextEdits>
     */
    public function format(TextDocument $document): Promise;
}
