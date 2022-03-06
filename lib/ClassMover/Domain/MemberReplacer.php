<?php

namespace Phpactor\ClassMover\Domain;

use Phpactor\ClassMover\Domain\Reference\MemberReferences;

interface MemberReplacer
{
    public function replaceMembers(SourceCode $source, MemberReferences $references, string $newName): SourceCode;
}
