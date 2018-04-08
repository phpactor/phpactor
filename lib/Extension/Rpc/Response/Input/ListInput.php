<?php

namespace Phpactor\Extension\Rpc\Response\Input;

use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;

class ListInput extends ChoiceInput
{
    public function type(): string
    {
        return 'list';
    }
}
