<?php

namespace Phpactor\Extension\Rpc\Response\Input;

class ListInput extends ChoiceInput
{
    private $allowMultipleResults = false;

    public function type(): string
    {
        return 'list';
    }

    public function withMultiple(bool $allowMultipleResults): self
    {
        $new = clone $this;
        $new->allowMultipleResults = $allowMultipleResults;

        return $new;
    }

    public function parameters(): array
    {
        return array_merge(parent::parameters(), [
            'multi' => $this->allowMultipleResults,
        ]);
    }
}
