<?php

namespace Phpactor\CodeTransform\Domain;

use Phpactor\CodeTransform\Domain\DocBlockUpdater\TagPrototype;

interface DocBlockUpdater
{
    public function set(string $docblock, TagPrototype $prototype): string;
}
