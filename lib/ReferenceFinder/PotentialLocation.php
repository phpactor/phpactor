<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\TextDocument\LocationRange;

final class PotentialLocation
{
    private const CONFIDENCE_SURELY = 'surely';
    private const CONFIDENCE_NOT = 'not';
    private const CONFIDENCE_MAYBE = 'maybe';

    public function __construct(private LocationRange $location, private string $confidence)
    {
    }

    public static function maybe(LocationRange $location): self
    {
        return new self($location, self::CONFIDENCE_MAYBE);
    }

    public static function not(LocationRange $location): self
    {
        return new self($location, self::CONFIDENCE_NOT);
    }

    public static function surely(LocationRange $location): self
    {
        return new self($location, self::CONFIDENCE_SURELY);
    }

    public function isSurely(): bool
    {
        return $this->confidence === self::CONFIDENCE_SURELY;
    }

    public function isMaybe(): bool
    {
        return $this->confidence === self::CONFIDENCE_MAYBE;
    }

    public function isNot(): bool
    {
        return $this->confidence === self::CONFIDENCE_NOT;
    }

    public function range(): LocationRange
    {
        return $this->location;
    }
}
