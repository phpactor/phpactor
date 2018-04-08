<?php

namespace Phpactor\Extension\Core\Console\Prompt;

use Symfony\Component\Process\ExecutableFinder;

class BashPrompt implements Prompt
{
    public function prompt(string $prompt, string $prefill): string
    {
        $cmd = sprintf(
            '%s -c "read -r -p %s -i %s -e RESPONSE; echo $RESPONSE;"',
            $this->getBashPath(),
            escapeshellarg($prompt),
            escapeshellarg($prefill)
        );

        // for some reason exec (?) doesn't like us using single quotes
        $cmd = str_replace('\'', '__QUOTE__', $cmd);
        $cmd = str_replace('"', '\'', $cmd);
        $cmd = str_replace('__QUOTE__', '"', $cmd);

        return exec($cmd);
    }

    public function name(): string
    {
        return 'bash';
    }

    public function isSupported()
    {
        return null !== $this->getBashPath();
    }

    private function getBashPath()
    {
        $executableFinder = new ExecutableFinder();

        return $executableFinder->find('bash');
    }
}
