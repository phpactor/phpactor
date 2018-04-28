<?php

namespace Phpactor\Extension\Navigation\Navigator;

class ChainNavigator implements Navigator
{
    /**
     * @var Navigator[]
     */
    private $navigators;

    public function __construct(array $navigators)
    {
        $this->navigators = $navigators;
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
