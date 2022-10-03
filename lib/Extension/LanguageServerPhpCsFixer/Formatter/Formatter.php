<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Formatter;

use Amp\Promise;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdits;

interface Formatter
{
    /**
     * @return Promise<TextEdits>
     */
    public function format(TextDocument $document): Promise;
}
