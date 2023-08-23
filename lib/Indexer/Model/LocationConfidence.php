<?php

namespace Phpactor\Indexer\Model;

use Phpactor\TextDocument\LocationRange;

class LocationConfidence
{
    public const CONFIDENCE_SURELY = 'surely';
    public const CONFIDENCE_NOT = 'not';
    public const CONFIDENCE_MAYBE = 'maybe';

    public function __construct(private LocationRange $range, private string $confidence)
    {
    }

    public function __toString(): string
    {
        return $this->confidence;
    }

    public static function maybe(LocationRange $range): self
    {
        return new self($range, self::CONFIDENCE_MAYBE);
    }

    public static function not(LocationRange $range): self
    {
        return new self($range, self::CONFIDENCE_NOT);
    }

    public static function surely(LocationRange $range): self
    {
        return new self($range, self::CONFIDENCE_SURELY);
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
        return $this->range;
    }
}
