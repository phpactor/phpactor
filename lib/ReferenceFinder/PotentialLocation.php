<?php

namespace Phpactor\ReferenceFinder;

use Phpactor\TextDocument\Location;

final class PotentialLocation
{
    private const CONFIDENCE_SURELY = 'surely';
    private const CONFIDENCE_NOT = 'not';
    private const CONFIDENCE_MAYBE = 'maybe';

    public function __construct(
        private readonly Location $location,
        private readonly string $confidence
    ) {
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
