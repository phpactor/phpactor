<?php

namespace Phpactor\Indexer\Model;

use Phpactor\TextDocument\Location;

class LocationConfidence
{
    public const CONFIDENCE_SURELY = 'surely';
    public const CONFIDENCE_NOT = 'not';
    public const CONFIDENCE_MAYBE = 'maybe';

    public function __construct(private Location $location, private string $confidence)
    {
    }

    public function __toString(): string
    {
        return $this->confidence;
    }

    public static function maybe(Location $location): self
    {
        return new self($location, self::CONFIDENCE_MAYBE);
    }

    public static function not(Location $location): self
    {
        return new self($location, self::CONFIDENCE_NOT);
    }

    public static function surely(Location $location): self
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

    public function location(): Location
    {
        return $this->location;
    }
}
