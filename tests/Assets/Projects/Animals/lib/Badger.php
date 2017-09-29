<?php

namespace Animals;

use Animals\Badger\Carnivorous;

class Badger
{
    const LODGING = 'set';

    private $carnivorous;

    public function __construct(Carnivorous $carnivorous)
    {
        $this->carnivorous = $carnivorous;
    }
    
    public function badge()
    {
        $this->badge();
    }

    public function carnivorous()
    {
    }
}
