<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class NamespaceName extends QualifiedName
{
    public static function root(): NamespaceName
    {
        return new self('');
    }
}
