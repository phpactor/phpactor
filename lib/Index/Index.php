<?php

namespace Phpactor\Index;

class Index
{
    private $map;
    private $paths;
    private $timestamp;

    public function __construct(array $paths, int $timestamp = null)
    {
        $this->paths = $paths;
        $this->timestamp = $timestamp ?: time();
    }

    public static function loadFromFile(string $path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf(
                'Index does not exist at path "%s"', $path
            ));
        }

        $index = unserialize(require($path));

        if (!$index instanceof Index) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid index object'
            ));
        }

        if (!$index) {
            throw new \RuntimeException(
                'Could not deserialize index, delete it and try again please.'
            );
        }

        return $index;
    }

    public function setMap(array $map)
    {
        $this->map = $map;
    }

    public function add($classFqn, $filePath)
    {
        $this->map[$classFqn] = $filePath;
    }

    public function getMap() 
    {
        return $this->map;
    }

    public function getPaths() 
    {
        return $this->paths;
    }

    public function getTimestamp() 
    {
        return $this->timestamp;
    }
}
