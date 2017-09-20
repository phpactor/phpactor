<?php

namespace Phpactor\Rpc\Handler;

class ClassNewHandler extends AbstractClassGenerateHandler
{
    protected function generate(array $arguments)
    {
        return $this->classGenerator->generate($arguments['new_path'], $arguments['variant'], (bool) $arguments['overwrite']);
    }

    public function name(): string
    {
        return 'class_new';
    }

    public function newMessage(): string
    {
        return 'Create at: ';
    }
}
