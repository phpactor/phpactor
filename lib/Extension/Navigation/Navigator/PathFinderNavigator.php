<?php

namespace Phpactor\Extension\Navigation\Navigator;

use Phpactor\PathFinder\Exception\NoMatchingSourceException;
use Phpactor\PathFinder\PathFinder;

class PathFinderNavigator implements Navigator
{
    private PathFinder $pathFinder;

    public function __construct(PathFinder $pathFinder)
    {
        $this->pathFinder = $pathFinder;
    }

    public function destinationsFor(string $path): array
    {
        try {
            return $this->pathFinder->destinationsFor($path);
        } catch (NoMatchingSourceException $e) {
            return [];
        }
    }
}
