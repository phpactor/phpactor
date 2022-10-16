<?php

namespace Phpactor\Extension\Symfony\Model;

class InMemorySymfonyContainerInspector implements SymfonyContainerInspector
{
    /**
     * @var SymfonyContainerService[]
     */
    private array $services;

    /**
     * @var SymfonyContainerParameter[]
     */
    private array $parameters;


    /**
     * @param SymfonyContainerService[] $services
     * @param SymfonyContainerParameter[] $parameters
     */
    public function __construct(array $services, array $parameters)
    {
        $this->services = $services;
        $this->parameters = $parameters;
    }

    public function services(): array
    {
        return $this->services;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function service(string $id): ?SymfonyContainerService
    {
        foreach ($this->services as $service) {
            if ($service->id === $id) {
                return $service;
            }
        }

        return null;
    }
}
