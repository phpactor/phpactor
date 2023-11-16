<?php

namespace Phpactor\Extension\CodeTransform\Rpc;

use Phpactor\CodeTransform\Domain\GenerateFromExisting;
use Phpactor\CodeTransform\Domain\SourceCode;

class ClassInflectHandler extends AbstractClassGenerateHandler
{
    public const NAME = 'class_inflect';

    public function name(): string
    {
        return self::NAME;
    }

    public function newMessage(): string
    {
        return 'Create inflection at: ';
    }

    protected function generate(array $arguments): SourceCode
    {
        $inflector = $this->generators->get($arguments[self::PARAM_VARIANT]);
        assert($inflector instanceof GenerateFromExisting);

        $currentClass = $this->className($arguments[self::PARAM_CURRENT_PATH]);
        $targetClass = $this->className($arguments[self::PARAM_NEW_PATH]);

        return $inflector->generateFromExisting(
            $currentClass,
            $targetClass
        );
    }
}
