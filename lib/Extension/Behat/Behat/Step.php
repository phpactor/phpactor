<?php

namespace Phpactor\Extension\Behat\Behat;

use Phpactor\Extension\Behat\Behat\Pattern\InvalidPatternException;
use Phpactor\Extension\Behat\Behat\Pattern\RegexPatternPolicy;
use Phpactor\Extension\Behat\Behat\Pattern\TurnipPatternPolicy;
use Phpactor\TextDocument\Location;

class Step
{
    public function __construct(
        private readonly Context $context,
        private readonly string $method,
        private readonly string $pattern,
        private readonly Location $location,
    ) {
    }

    public function context(): Context
    {
        return $this->context;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function pattern(): string
    {
        return $this->pattern;
    }

    public function matches(string $line): bool
    {
        $policies = [
            new TurnipPatternPolicy(),
            new RegexPatternPolicy(),
        ];

        foreach ($policies as $policy) {
            try {
                $regex = $policy->transformPatternToRegex($this->pattern);
            } catch (InvalidPatternException) {
                continue;
            }

            if (preg_match($regex, $line)) {
                return true;
            }
        }

        return false;
    }

    public function location(): Location
    {
        return $this->location;
    }
}
