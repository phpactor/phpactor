<?php

namespace Phpactor\Search\Model\Constraint;

use Phpactor\Search\Model\TokenConstraint;
use RuntimeException;

abstract class AbstractConstraint implements TokenConstraint
{
    protected string $placeholder;

    protected string $value;

    final public function __construct(string $placeholder, string $value)
    {
        $this->placeholder = $placeholder;
        $this->value = $value;
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
}
