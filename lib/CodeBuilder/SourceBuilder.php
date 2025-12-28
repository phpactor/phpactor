<?php

namespace Phpactor\CodeBuilder;

use Phpactor\CodeBuilder\Domain\Prototype;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\TextDocument\TextDocument;

class SourceBuilder
{
    public function __construct(
        private Renderer $generator,
        private Updater $updater
    ) {
    }

    public function render(Prototype\Prototype $prototype): TextDocument
    {
        return $this->generator->render($prototype);
    }

    public function apply(Prototype\Prototype $prototype, TextDocument $code): string
    {
        return $this->updater->textEditsFor($prototype, $code)->apply($code);
    }
}
