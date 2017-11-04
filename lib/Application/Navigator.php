<?php

namespace Phpactor\Application;

use Phpactor\ClassFileConverter\PathFinder;
use RuntimeException;

class Navigator
{
    /**
     * @var PathFinder
     */
    private $pathFinder;

    public function __construct(PathFinder $pathFinder)
    {
        $this->pathFinder = $pathFinder;
    }

    public function destinationsFor(string $path)
    {
        return $this->pathFinder->destinationsFor($path);
    }

    public function canCreateNew(string $path, string $destinationName)
    {
        $destinations = $this->destinationsFor($path);

        if (false === isset($destinations[$destinationName])) {
            throw new RuntimeException(sprintf(
                'Destination "%s" does not exist, known destinations: "%s"',
                $destinationName, implode('", "', array_keys($destinations))
            ));
        }

        $destination = $destinations[$destinationName];

        if (file_exists($destination)) {
            return false;
        }

        return true;
    }
}
