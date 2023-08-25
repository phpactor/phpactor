<?php

namespace Phpactor\Extension\Behat\Behat;

use Phpactor\Extension\Behat\Behat\Pattern\InvalidPatternException;
use Phpactor\Extension\Behat\Behat\Pattern\RegexPatternPolicy;
use Phpactor\Extension\Behat\Behat\Pattern\TurnipPatternPolicy;

class Step
{
    public function __construct(
        private Context $context,
        private string $method,
        private string $pattern,
        private string $path,
        private int $startByteOffset,
        private int $endByteOffset
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

    public function path(): string
    {
        return $this->path;
    }

    public function start(): int
    {
        return $this->startByteOffset;
    }

    public function end(): int
    {
        return $this->endByteOffset;
    }
}
