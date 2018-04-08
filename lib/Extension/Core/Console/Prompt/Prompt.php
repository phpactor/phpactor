<?php

namespace Phpactor\Extension\Core\Console\Prompt;

/**
 * Prompt the user for a value, ideally prefilling the prompt.
 *
 * For example when moving or renaming a file you do not want to type
 * the whole path again.
 */
interface Prompt
{
    public function prompt(string $prompt, string $default): string;

    public function name(): string;

    public function isSupported();
}
