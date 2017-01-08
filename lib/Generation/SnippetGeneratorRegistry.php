<?php

namespace Phpactor\Generation;

use Sylius\Component\Registry\ServiceRegistry;

class SnippetGeneratorRegistry extends ServiceRegistry
{
    public function __construct()
    {
        parent::__construct(
            SnippetGeneratorInterface::class,
            'generator'
        );
    }
}
