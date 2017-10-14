<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Webmozart\Glob\Glob;

class ClassInflectHandler extends AbstractClassGenerateHandler
{
    const NAME = 'class_inflect';

    protected function generate(array $arguments)
    {
        if (Glob::isDynamic($arguments['current_path'])) {
            throw new \RuntimeException(sprintf(
                'The RPC handler does not support globs (%s), use the Phpactor CLI client',
                $arguments['current_path']
            ));
        }

        $newPaths = $this->classGenerator->generateFromExisting(
            $arguments[self::PARAM_CURRENT_PATH],
            $arguments[self::PARAM_NEW_PATH],
            $arguments[self::PARAM_VARIANT],
            (bool) $arguments[self::PARAM_OVERWRITE]
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
        return self::NAME;
    }

    public function newMessage(): string
    {
        return 'Create inflection at: ';
    }
}
