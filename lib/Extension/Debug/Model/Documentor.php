<?php

namespace Phpactor\Extension\Debug\Model;

interface Documentor
{
    public function document(string $commandName = ''): string;
}
