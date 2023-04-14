<?php

namespace Phpactor\WorseReflection\Core;

final class Visibility
{
    private function __construct(
        private string $visibility
    ) {
    }

    public function __toString(): string
    {
        return $this->visibility;
    }

    public static function public(): self
    {
        return self::create('public');
    }

    public static function private(): self
    {
        return self::create('private');
    }

    public static function protected(): self
    {
        return self::create('protected');
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function isProtected(): bool
    {
        return $this->visibility === 'protected';
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    private static function create(string $visibility): self
    {
        return new self($visibility);
    }
}
