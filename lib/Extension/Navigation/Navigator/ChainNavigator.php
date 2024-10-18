<?php

namespace Phpactor\Extension\Navigation\Navigator;

class ChainNavigator implements Navigator
{
    /**
     * @param Navigator[] $navigators
     */
    public function __construct(private array $navigators)
    {
    }

    public function destinationsFor(string $path): array
    {
        $destinations = [];
        foreach ($this->navigators as $navigator) {
            $destinations = array_merge($destinations, $navigator->destinationsFor($path));
        }

        return $destinations;
    }
}
