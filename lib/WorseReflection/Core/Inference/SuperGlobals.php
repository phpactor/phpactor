<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

final class SuperGlobals
{
    /**
     * @return array<string,Type>
     */
    public static function list(): array
    {
        return [
            'GLOBALS' => TypeFactory::array(),
            '_SERVER' => TypeFactory::array(),
            '_GET' => TypeFactory::array(),
            '_POST' => TypeFactory::array(),
            '_FILES' => TypeFactory::array(),
            '_COOKIE' => TypeFactory::array(),
            '_SESSION' => TypeFactory::array(),
            '_REQUEST' => TypeFactory::array(),
            '_ENV' => TypeFactory::array(),

            'argc' => TypeFactory::int(),
            'argv' => TypeFactory::array(TypeFactory::string()),
        ];
    }
}
