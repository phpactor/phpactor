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
use Webmozart\Glob\Glob;

class ClassInflectHandler extends AbstractClassGenerateHandler
{
    protected function generate(array $arguments)
    {
        if (Glob::isDynamic($arguments['current_path'])) {
            throw new \RuntimeException(sprintf(
                'The RPC handler does not support globs (%s), use the Phpactor CLI client',
                $arguments['current_path']
            ));
        }

        $newPaths = $this->classGenerator->generateFromExisting(
            $arguments['current_path'],
            $arguments['new_path'],
            $arguments['variant'],
            (bool) $arguments['overwrite']
        );

        if (count($newPaths) !== 1) {
            throw new \RuntimeException(sprintf(
                'Expected 1 path from class generator, got %s',
                count($newPaths)
            ));
        }

        return reset($newPaths);
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

