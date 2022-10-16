<?php

namespace Phpactor\Extension\Symfony\Model;

interface SymfonyContainerInspector
{
    /**
     * @return SymfonyContainerService[]
     */
    public function services(): array;
}
