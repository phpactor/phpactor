<?php

namespace Phpactor\Search\Model;

class MatchResult
{
    private ?bool $result;
   
    public function __construct(?bool $result)
    {
        $this->result = $result;
    }

    public static function yes(): self
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

    public function isNotMatch(): bool
    {
        return $this->result === false;
    }

    public function isMatch(): bool
    {
        return $this->result === true;
    }

    public static function fromBool(bool $bool): self
    {
        return new self($bool);
    }

}
