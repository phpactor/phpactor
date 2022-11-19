<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport;

use Phpactor\CodeTransform\Domain\NameWithByteOffset;

class NameCandidate
{
    public function __construct(
        private NameWithByteOffset $unresolvedName,
        private string $candidateFqn
    ) {
    }

    public function candidateFqn(): string
    {
        return $this->candidateFqn;
    }

    public function unresolvedName(): NameWithByteOffset
    {
        return $this->unresolvedName;
    }
}
