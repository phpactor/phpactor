<?php

namespace Phpactor\CodeBuilder;

use Phpactor\CodeBuilder\Domain\Prototype;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Code;

class SourceBuilder
{
    private Renderer $generator;

    private Updater $updater;

    public function __construct(
        Renderer $generator,
        Updater $updater
    ) {
        $this->generator = $generator;
        $this->updater = $updater;
    }

    public function render(Prototype\Prototype $prototype)
    {
        return $this->generator->render($prototype);
    }

    public function apply(Prototype\Prototype $prototype, Code $code)
    {
        return $this->updater->textEditsFor($prototype, $code)->apply($code);
    }
}
