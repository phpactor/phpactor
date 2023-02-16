<?php

namespace Phpactor\CodeBuilder\Domain;

use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\TextDocument\TextDocument;

interface BuilderFactory
{
    public function fromSource(TextDocument|string $source): SourceCodeBuilder;
}
