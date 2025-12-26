<?php

namespace Phpactor\ReferenceFinder\Search;

use RuntimeException;

final class NameSearchResultType
{
    public const TYPE_CLASS = 'class';
    public const TYPE_FUNCTION = 'function';
    public const TYPE_CONSTANT = 'constant';

    private readonly string $type;

    public function __construct(string $type)
    {
        $validTypes = [self::TYPE_FUNCTION, self::TYPE_CLASS, self::TYPE_CONSTANT];
        if (!in_array($type, $validTypes)) {
            throw new RuntimeException(sprintf(
                'Name search result type "%s" is invalid, must be one of "%s"',
                $type,
                implode('", "', $validTypes)
            ));
        }
        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->type;
    }

    public function isClass(): bool
    {
        return $this->type === self::TYPE_CLASS;
    }

    public function isFunction(): bool
    {
        return $this->type === self::TYPE_FUNCTION;
    }

    public function isConstant(): bool
    {
        return $this->type === self::TYPE_CONSTANT;
    }
}
