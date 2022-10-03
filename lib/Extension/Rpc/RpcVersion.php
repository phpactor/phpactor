<?php

namespace Phpactor\Extension\Rpc;

class RpcVersion
{
    public const MAJOR = 1;
    public const MINOR = 0;
    public const PATCH = 0;

    public static function asString()
    {
        return implode('.', [
            self::MAJOR,
            self::MINOR,
            self::PATCH
        ]);
    }
}
