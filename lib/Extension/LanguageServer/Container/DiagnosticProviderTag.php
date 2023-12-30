<?php

namespace Phpactor\Extension\LanguageServer\Container;

class DiagnosticProviderTag
{
    public const NAME = 'name';
    public const OUTSOURCE = 'outsource';

    /**
     * @param bool $outsource if this diagnostic provider should be outsourced to different process.
     * @return array{name:string,outsource:bool}
     */
    public static function create(string $name, bool $outsource = false): array
    {
        return [
            self::NAME  => $name,
            self::OUTSOURCE => $outsource,
        ];
    }
}
