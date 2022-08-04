<?php

namespace Phpactor\Completion\Core;

/**
 * Format labels for fully qualified names.
 *
 * For example: we probably don't want to show the entire FQN in completion
 * result labels, but we also need to make sure they are no ambiguous.
 */
interface LabelFormatter
{
    /**
     * @param array<string,bool> $seen
     */
    public function format(string $name, array $seen, int $offset = 1): string;
}
