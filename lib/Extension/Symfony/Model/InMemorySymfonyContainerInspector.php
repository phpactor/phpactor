<?php

namespace Phpactor\Extension\Symfony\Model;

class InMemorySymfonyContainerInspector implements SymfonyContainerInspector
{
    /**
     * @var SymfonyContainerService[]
     */
    private array $services;

    /**
     * @param SymfonyContainerService[] $services
     */
    public function __construct(array $services)
    {
        $this->services = $services;
    }

    public function services(): array
    {
        return $this->services;
    }
}
