<?php

namespace Phpactor\Extension\LanguageServer\Server;

interface StdOut
{
    public function write(string $buffer): void;

    public function writeln(string $buffer): void;
}
