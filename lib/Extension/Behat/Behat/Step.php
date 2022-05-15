<?php

namespace Phpactor\Extension\Behat\Behat;

use Behat\Behat\Definition\Exception\InvalidPatternException;
use Behat\Behat\Definition\Pattern\Policy\RegexPatternPolicy;
use Behat\Behat\Definition\Pattern\Policy\TurnipPatternPolicy;

class Step
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
     */
    private $path;

    /**
     * @var int
     */
    private $startByteOffset;

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

    public function matches(string $line)
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
