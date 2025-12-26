<?php

namespace Phpactor\Completion\Core;

class ParameterInformation
{
    /**
     *  @param ?string $documentation
     *      The human-readable doc-comment of this signature. Will be shown
     *      in the UI but can be omitted.
     */
    public function __construct(
        private readonly string $label,
        private readonly ?string $documentation = null
    ) {
    }

    public function documentation(): ?string
    {
        return $this->documentation;
    }

    public function label(): string
    {
        return $this->label;
    }
}
