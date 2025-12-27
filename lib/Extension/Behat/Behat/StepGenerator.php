<?php

namespace Phpactor\Extension\Behat\Behat;

use Generator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<Step>
 */
class StepGenerator implements IteratorAggregate
{
    public function __construct(
        private readonly BehatConfig $config,
        private readonly StepFactory $factory,
        private readonly StepParser $parser
    ) {
    }

    public function getIterator(): Generator
    {
        yield from $this->factory->generate($this->parser, $this->config->contexts());
    }
}
