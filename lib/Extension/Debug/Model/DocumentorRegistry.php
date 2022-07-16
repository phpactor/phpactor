<?php

namespace Phpactor\Extension\Debug\Model;

use InvalidArgumentException;
use Phpactor\Container\Container;

class DocumentorRegistry
{
    /** @var array<string> */
    private array $documentors;

    private Container $container;

    /**
     * @param array<string> $documentors
     */
    public function __construct(Container $container, array $documentors)
    {
        $this->documentors = $documentors;
        $this->container = $container;
    }

    public function get(string $string): Documentor
    {
        if (!array_key_exists($string, $this->documentors)) {
            throw new InvalidArgumentException(
                'Could not find documentor. Available documentors: ' . implode(', ', array_keys($this->documentors))
            );
        }

        return $this->container->get($this->documentors[$string]);
    }
}
