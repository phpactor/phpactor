<?php

namespace Phpactor\CodeBuilder\Domain;

use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdits;

interface Updater
{
    public function textEditsFor(Prototype $prototype, TextDocument $code): TextEdits;
}
