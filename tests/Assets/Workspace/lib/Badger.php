<?php

namespace Animals;

use Animals\Aardvark\Insectarian;

class Badger
{
    private $carnivorous;

    public function __construct(Insectarian $carnivorous)
    {
        $this->carnivorous = $carnivorous;
    }
}
