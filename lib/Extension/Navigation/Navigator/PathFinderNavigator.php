<?php

namespace Phpactor\Extension\Navigation\Navigator;

use Phpactor\PathFinder\Exception\NoMatchingSourceException;
use Phpactor\PathFinder\PathFinder;

class PathFinderNavigator implements Navigator
{
    public function __construct(private readonly PathFinder $pathFinder)
    {
    }

    public function destinationsFor(string $path): array
    {
        try {
            return $this->pathFinder->destinationsFor($path);
        } catch (NoMatchingSourceException) {
            return [];
        }
    }
}
