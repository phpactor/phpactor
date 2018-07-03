<?php

namespace Phpactor\Extension\Rpc;

interface InterfactiveHandler extends Handler
{
    public function dialog(Dialog $dialog, Arguments $arguments);
}
