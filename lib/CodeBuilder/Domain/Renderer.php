<?php

namespace Phpactor\CodeBuilder\Domain;

use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\TextDocument\TextDocument;

interface Renderer
{
    public function render(Prototype $prototype, ?string $variant = null): TextDocument;
}
