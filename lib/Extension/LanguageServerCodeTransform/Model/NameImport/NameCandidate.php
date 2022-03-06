<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport;

use Phpactor\CodeTransform\Domain\NameWithByteOffset;

class NameCandidate
{
    /**
     * @var NameWithByteOffset
     */
    private $unresolvedName;
    /**
     * @var string
     */
    private $candidateFqn;

    public function __construct(NameWithByteOffset $name, string $candidateFqn)
    {
        $this->unresolvedName = $name;
        $this->candidateFqn = $candidateFqn;
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
