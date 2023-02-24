<?php

namespace Phpactor\Configurator\Config;

use RuntimeException;
use stdClass;

final class JsonConfig
{
    private function __construct(private stdClass $object)
    {
    }

    public function has(string $key): bool
    {
        return isset($this->object->$key);
    }

    public static function fromPath(string $path): JsonConfig
    {
        if (!file_exists($path)) {
            throw new RuntimeException(sprintf(
                'File "%s" does not exist',
                $path
            ));
        }

        $contents = file_get_contents($path);

        if (false === $contents) {
            throw new RuntimeException(sprintf(
                'Could not read file "%s"',
                $path
            ));
        }

        $obj = json_decode($contents);

        if (!$obj instanceof stdClass) {
            return new self(new stdClass());
        }

        return new self($obj);
    }
}
