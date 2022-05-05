<?php

namespace Phpactor\WorseReflection\Core;

class Trinary
{
    private ?bool $true;

    private bool $maybe;

    private function __construct(?bool $true)
    {
        $this->true = $true;
        // when using === comparison null and false equate
        // to the same thing :/
        $this->maybe = null === $true;
    }

    public static function true(): self
    {
        return new self(true);
    }

    public static function false(): self
    {
        return new self(false);
    }

    public static function maybe(): self
    {
        return new self(null);
    }

    public static function fromBoolean(bool $bool): self
    {
        if ($bool) {
            return self::true();
        }

        return self::false();
    }

    public function isTrue(): bool
    {
        return $this->true === true;
    }

    public function isFalse(): bool
    {
        return $this->true === false;
    }

    public function isMaybe(): bool
    {
        return $this->maybe === true;
    }
}
