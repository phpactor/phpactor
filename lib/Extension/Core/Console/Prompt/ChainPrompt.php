<?php

namespace Phpactor\Extension\Core\Console\Prompt;

use RuntimeException;

final class ChainPrompt implements Prompt
{
    private $prompts;

    public function __construct(array $prompts)
    {
        foreach ($prompts as $prompt) {
            $this->addPrompt($prompt);
        }
    }

    public function prompt(string $prompt, string $prefill): string
    {
        foreach ($this->prompts as $prompter) {
            if (false === $prompter->isSupported()) {
                continue;
            }

            return $prompter->prompt($prompt, $prefill);
        }

        throw new RuntimeException(sprintf(
            'Could not prompt for "%s". '.
            'Appropriate prompt implementation for your platform / environment could not be found (tried "%s"). '.
            'Try specifying the command in full',
            $prompt,
            implode('", "', array_keys($this->prompts))
        ));
    }

    public function isSupported()
    {
        return true;
    }

    public function name(): string
    {
        return 'chain';
    }

    private function addPrompt(Prompt $prompt): void
    {
        $this->prompts[$prompt->name()] = $prompt;
    }
}
