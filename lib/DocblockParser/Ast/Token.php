<?php

namespace Phpactor\DocblockParser\Ast;

final class Token implements Element
{
    public const T_PHPDOC_OPEN = 'PHPDOC_OPEN';
    public const T_ASTERISK = 'PHPDOC_LEADING';
    public const T_AMPERSAND = 'AMPERSAND';
    public const T_PHPDOC_CLOSE = 'PHPDOC_CLOSE';
    public const T_VARIABLE = 'VARIABLE';
    public const T_UNKNOWN = 'UNKNOWN';
    public const T_BANG = 'BANG';
    public const T_NULLABLE = 'NULLABLE';
    public const T_BAR = 'BAR';
    public const T_TAG = 'TAG';
    public const T_EQUALS = 'EQUALS';
    public const T_COLON = 'COLON';
    public const T_DOUBLE_COLON = 'DOUBLE_COLON';
    public const T_COMMA = 'COMMA';
    public const T_LIST = 'LIST';
    public const T_LABEL = 'LABEL';
    public const T_INTEGER = 'INTEGER';
    public const T_FLOAT = 'FLOAT';
    public const T_WHITESPACE = 'WHITESPACE';
    public const T_BRACKET_SQUARE_OPEN = 'BRACKET_SQUARE_OPEN';
    public const T_BRACKET_SQUARE_CLOSE = 'BRACKET_SQUARE_CLOSE';
    public const T_BRACKET_ANGLE_OPEN = 'BRACKET_ANGLE_OPEN';
    public const T_BRACKET_ANGLE_CLOSE = 'BRACKET_ANGLE_CLOSE';
    public const T_BRACKET_CURLY_OPEN = 'BRACKET_CURLY_OPEN';
    public const T_BRACKET_CURLY_CLOSE = 'BRACKET_CURLY_CLOSE';
    public const T_QUOTED_STRING = 'QUOTED_STRING';
    public const T_PAREN_OPEN = 'PAREN_OPEN';
    public const T_PAREN_CLOSE = 'PAREN_CLOSE';
    public const T_INVALID = 'INVALID';
    public const T_IS = 'IS';

    public function __construct(public int $byteOffset, public string $type, public string $value)
    {
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function start(): int
    {
        return $this->byteOffset;
    }

    public function end(): int
    {
        return $this->byteOffset + strlen($this->value);
    }

    public function length(): int
    {
        return $this->end() - $this->start();
    }
}
