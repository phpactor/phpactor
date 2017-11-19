<?php

namespace Phpactor\Rpc\Response\Input;

class ListInput extends ChoiceInput
{
    public function type(): string
    {
        return 'list';
    }
}
