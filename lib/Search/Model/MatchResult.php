<?php

namespace Phpactor\Search\Model;

class MatchResult
{
    public ?MatchToken $token;
    public ?string $name;
    private ?bool $result;

    private function __construct(?bool $result, ?MatchToken $token = null, ?string $name = null)
    {
        $this->result = $result;
        $this->token = $token;
        $this->name = $name;
    }

    public static function yes(MatchToken $token, string $name = null): self
    {
        return new self(true);
    }

    public static function no(): self
    {
        return new self(false);
    }

    public static function maybe(): self
    {
        return new self(null);
    }

    public function isNo(): bool
    {
        return $this->result === false;
    }

    public function isYes(): bool
    {
        return $this->result === true;
    }

    public static function fromBool(bool $bool): self
    {
        return new self($bool);
    }

    public function isMaybe(): bool
    {
        return $this->result === null;
    }
}
