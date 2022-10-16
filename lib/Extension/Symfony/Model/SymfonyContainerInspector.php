<?php

namespace Phpactor\Extension\Symfony\Model;

interface SymfonyContainerInspector
{
    /**
     * @return SymfonyContainerService[]
     */
    public function services(): array;

    /**
     * @return SymfonyContainerParameter[]
     */
    public function parameters(): array;

    public function service(string $id): ?SymfonyContainerService;
}
