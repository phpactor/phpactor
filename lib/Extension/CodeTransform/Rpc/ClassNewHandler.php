<?php

namespace Phpactor\Extension\CodeTransform\Rpc;

use Phpactor\Extension\CodeTransform\Rpc\AbstractClassGenerateHandler;

class ClassNewHandler extends AbstractClassGenerateHandler
{
    const NAME = 'class_new';

    protected function generate(array $arguments)
    {
        return $this->classGenerator->generate($arguments['new_path'], $arguments['variant'], (bool) $arguments['overwrite']);
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function newMessage(): string
    {
        return 'Create at: ';
    }
}
