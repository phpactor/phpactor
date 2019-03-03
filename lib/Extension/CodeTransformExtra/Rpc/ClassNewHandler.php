<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\SourceCode;

class ClassNewHandler extends AbstractClassGenerateHandler
{
    const NAME = 'class_new';

    protected function generate(array $arguments): SourceCode
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
