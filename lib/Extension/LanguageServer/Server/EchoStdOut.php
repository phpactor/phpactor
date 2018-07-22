<?php

namespace Phpactor\Extension\LanguageServer\Server;

class EchoStdOut implements StdOut
{
    public function write(string $buffer): void
    {
        echo $buffer;
    }

    public function writeln(string $buffer): void
    {
        echo $buffer . PHP_EOL;
    }
}
