<?php

namespace Phpactor\Extension\Behat\Behat;

use Generator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<Step>
 */
class StepGenerator implements IteratorAggregate
{
    private BehatConfig $config;

    private StepParser $parser;

    private StepFactory $factory;

    public function __construct(BehatConfig $config, StepFactory $factory, StepParser $parser)
    {
        $this->config = $config;
        $this->parser = $parser;
        $this->factory = $factory;
    }

    public function getIterator(): Generator
    {
        yield from $this->factory->generate($this->parser, $this->config->contexts());
    }
}
