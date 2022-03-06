<?php

namespace Phpactor\Extension\Php\Model;

interface PhpVersionResolver
{
    /**
     * @return string
     */
    public function resolve(): ?string;
}
