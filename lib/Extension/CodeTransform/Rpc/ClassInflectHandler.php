<?php

namespace Phpactor\Extension\CodeTransform\Rpc;

use Phpactor\CodeTransform\Domain\SourceCode;
use Webmozart\Glob\Glob;

class ClassInflectHandler extends AbstractClassGenerateHandler
{
    const NAME = 'class_inflect';

    protected function generate(array $arguments): SourceCode
    {
        if (Glob::isDynamic($arguments['current_path'])) {
            throw new \RuntimeException(sprintf(
                'The RPC handler does not support globs (%s), use the Phpactor CLI client',
                $arguments['current_path']
            ));
        }

        $newCodes = $this->classGenerator->generateFromExisting(
            $arguments[self::PARAM_CURRENT_PATH],
            $arguments[self::PARAM_NEW_PATH],
            $arguments[self::PARAM_VARIANT],
            (bool) $arguments[self::PARAM_OVERWRITE]
        );

        if (count($newCodes) !== 1) {
            throw new \RuntimeException(sprintf(
                'Expected 1 path from class generator, got %s',
                count($newCodes)
            ));
        }

        return reset($newCodes);
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function newMessage(): string
    {
        return 'Create inflection at: ';
    }
}
