<?php

namespace Phpactor\CodeTransform\Domain;

use Phpactor\CodeTransform\Domain\DocBlockUpdater\TagPrototype;
use Phpactor\WorseReflection\Core\Type;

interface DocBlockUpdater
{
    public function set(string $docblock, TagPrototype $prototype): string;
}
