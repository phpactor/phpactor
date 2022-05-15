<?php

namespace Phpactor\Extension\Behat\Behat;

interface ContextClassResolver
{
    public function resolve(string $className): string;
}
