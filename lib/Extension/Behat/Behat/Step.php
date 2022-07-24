<?php

namespace Phpactor\Extension\Behat\Behat;

use Phpactor\Extension\Behat\Behat\Pattern\InvalidPatternException;
use Phpactor\Extension\Behat\Behat\Pattern\RegexPatternPolicy;
use Phpactor\Extension\Behat\Behat\Pattern\TurnipPatternPolicy;

class Step
{
    private Context $context;

    private string $method;

    private string $pattern;

    private string $path;

    private int $startByteOffset;

    public function __construct(
        Context $context,
        string $method,
        string $pattern,
        string $path,
        int $startByteOffset
    ) {
        $this->context = $context;
        $this->method = $method;
        $this->pattern = $pattern;
        $this->path = $path;
        $this->startByteOffset = $startByteOffset;
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
            } catch (InvalidPatternException $invalid) {
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

    public function byteOffset(): int
    {
        return $this->startByteOffset;
    }
}
