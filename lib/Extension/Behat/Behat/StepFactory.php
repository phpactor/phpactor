<?php

namespace Phpactor\Extension\Behat\Behat;

use Generator;

interface StepFactory
{
    public function generate(StepParser $parser, array $contexts): Generator;
}
