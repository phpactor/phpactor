<?php

namespace Phpactor\WorseReflection\Core;

class Visibility
{
    private $visibility;

    private function __construct()
    {
    }

    public function __toString()
    {
        return $this->visibility;
    }

    public static function public()
    {
        return self::create('public');
    }

    public static function private()
    {
        return self::create('private');
    }

    public static function protected()
    {
        return self::create('protected');
    }

    public function isPublic()
    {
        return $this->visibility === 'public';
    }

    public function isProtected()
    {
        return $this->visibility === 'protected';
    }

    public function isPrivate()
    {
        return $this->visibility === 'private';
    }

    private static function create($visibility)
    {
        $instance = new self();
        $instance->visibility = $visibility;

        return $instance;
    }
}
