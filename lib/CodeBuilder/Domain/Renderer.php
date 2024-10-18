<?php

namespace Phpactor\CodeBuilder\Domain;

use Phpactor\CodeBuilder\Domain\Prototype\Prototype;

interface Renderer
{
    public function render(Prototype $prototype, ?string $variant = null): Code;
}
