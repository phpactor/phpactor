<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\ClassNew;
use Phpactor\Rpc\Editor\Input\TextInput;
use Phpactor\Rpc\Editor\Input\ChoiceInput;
use Phpactor\Rpc\Editor\StackAction;
use Phpactor\Rpc\Editor\InputCallbackAction;
use Phpactor\Rpc\ActionRequest;
use Phpactor\Application\Exception\FileAlreadyExists;
use Phpactor\Rpc\Editor\OpenFileAction;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Editor\Input\ConfirmInput;

class ClassInflectHandler extends AbstractClassGenerateHandler
{
    protected function generate(array $arguments)
    {
        return $this->classGenerator->generateFromExisting(
            $arguments['current_path'],
            $arguments['new_path'],
            $arguments['variant'],
            (bool) $arguments['overwrite']
        );
    }

    public function name(): string
    {
        return 'class_inflect';
    }

    public function newMessage(): string
    {
        return 'Create inflection at: ';
    }
}

