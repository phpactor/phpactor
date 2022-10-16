<?php

namespace Phpactor\Extension\Symfony\Adapter\Symfony;

use Phpactor\Extension\Symfony\Model\SymfonyContainerInspector;

class XmlSymfonyContainerInspector implements SymfonyContainerInspector
{
    private string $xmlPath;

    public function __construct(string $xmlPath)
    {
        $this->xmlPath = $xmlPath;
    }

    public function services(): array
    {
        return [];
    }
}
