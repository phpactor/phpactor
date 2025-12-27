<?php

namespace Phpactor\Extension\Debug\Model;

use InvalidArgumentException;
use Phpactor\Container\Container;

class DocumentorRegistry
{
    /**
     * @param array<string> $documentors
     */
    public function __construct(
        private readonly Container $container,
        private array $documentors
    ) {
    }

    public function get(string $string): Documentor
    {
        if (!array_key_exists($string, $this->documentors)) {
            throw new InvalidArgumentException(
                'Could not find documentor. Available documentors: ' . implode(', ', array_keys($this->documentors))
            );
        }

        return $this->container->expect($this->documentors[$string], Documentor::class);
    }
}
