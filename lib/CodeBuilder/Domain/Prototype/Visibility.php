<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use InvalidArgumentException;

final class Visibility
{
    const PUBLIC = 'public';
    const PROTECTED = 'protected';
    const PRIVATE = 'private';
    const VISIBILITIES = [
        self::PUBLIC,
        self::PROTECTED,
        self::PRIVATE
    ];

    private readonly string $visibility;

    private function __construct(string $visibility)
    {
        if (!in_array($visibility, self::VISIBILITIES)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid visibility "%s", valid visibilities: "%s"',
                $visibility,
                implode('", "', self::VISIBILITIES)
            ));
        }

        $this->visibility = $visibility;
    }

    public function __toString(): string
    {
        return $this->visibility;
    }

    public static function fromString(string $string): self
    {
        return new self($string);
    }

    public static function private(): self
    {
        return new self(self::PRIVATE);
    }

    public static function protected(): self
    {
        return new self(self::PROTECTED);
    }

    public static function public(): self
    {
        return new self(self::PUBLIC);
    }
}
