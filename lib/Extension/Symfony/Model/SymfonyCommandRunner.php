<?php

namespace Phpactor\Extension\Symfony\Model;

final class SymfonyCommandRunner
{
    public static function run(string $command, string... $args): ?string
    {
        $escapedArgs = implode(' ', array_map('escapeshellarg', $args));

        $cmd = sprintf(
            'bin/console %s %s',
            $command,
            $escapedArgs,
        );

        $result = shell_exec($cmd);
        if (!$result) {
            return null;
        }

        return $result;
    }
}
