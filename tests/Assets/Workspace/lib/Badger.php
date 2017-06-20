<?php

namespace Animals;

use Animals\Badger\Carnivorous;

class Badger
{
    private $carnivorous;

    public function __construct(Carnivorous $carnivorous)
    {
        $this->carnivorous = $carnivorous;
    }
}
