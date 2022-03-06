<?php

namespace Phpactor\Completion\Core;

class ParameterInformation
{
    /**
     * The label of this signature. Will be shown in
     * the UI.
     *
     * @var string
     */
    private $label;

    /**
     * The human-readable doc-comment of this signature. Will be shown
     * in the UI but can be omitted.
     *
     * @var string|null
     */
    private $documentation;

    public function __construct(string $label, string $documentation = null)
    {
        $this->label = $label;
        $this->documentation = $documentation;
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
