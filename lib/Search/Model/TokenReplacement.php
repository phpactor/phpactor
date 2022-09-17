<?php

namespace Phpactor\Search\Model;

use RuntimeException;

final class TokenReplacement
{
    private string $placeholder;
    private string $replacement;

    public function __construct(string $placeholder, string $replacement)
    {
        $this->placeholder = $placeholder;
        $this->replacement = $replacement;
    }

    public static function fromString(string $constraint): static
    {
        $parts = explode(':', $constraint);
        if (count($parts) !== 2) {
            throw new RuntimeException(sprintf(
                'Invalid specification, must be of form <placeholder>:<value>, got: %s',
                $constraint
            ));
        }

        return new static($parts[0], $parts[1]);
    }

    public function placeholder(): string
    {
        return $this->placeholder;
    }

    public function replacement(): string
    {
        return $this->replacement;
    }
}
