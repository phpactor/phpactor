<?php

namespace Phpactor\Configurator\Model;

use RuntimeException;
use stdClass;

class JsonConfig
{
    private bool $loaded = false;

    private stdClass $object;

    private function __construct(private string $path)
    {
        $this->object = new stdClass();
    }

    public function has(string $key): bool
    {
        $this->load();
        return isset($this->object->$key);
    }

    public static function fromPath(string $path): JsonConfig
    {
        return new self($path);
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }
        $this->loaded = true;

        if (!file_exists($this->path)) {
            return;
        }

        $contents = file_get_contents($this->path);

        if (false === $contents) {
            return;
        }

        $obj = json_decode($contents);

        if (!$obj instanceof stdClass) {
            return;
        }

        $this->object = $obj;
    }
}
