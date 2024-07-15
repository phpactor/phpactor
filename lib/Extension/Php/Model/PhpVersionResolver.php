<?php

namespace Phpactor\Extension\Php\Model;

interface PhpVersionResolver
{
    public function resolve(): ?string;
    public function name(): string;
}
