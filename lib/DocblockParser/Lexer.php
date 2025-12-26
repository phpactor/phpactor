<?php

namespace Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\Tokens;
use RuntimeException;

final class Lexer
{
    private const PATTERN_LABEL = '[a-zA-Z\\\][-a-zA-Z0-9_\\\\\*]*';

    /**
     * @var string[]
     */
    private const PATTERNS = [
        '/\*+', // start tag
        '\*/', // close tag
        ' {1}\* {1}',
        '\[\]', // list
        '\?', //tag
        '@[\w-]+', //tag
        '\R', // newline
        ' *', // space
        '::', // double colon
        ',', // comma
        '\|', // bar (union)
        '=', // equals
        '(', ')', '\{', '\}', '\[', '\]', '<', '>', // brackets
        '\$[a-zA-Z0-9_\x80-\xff]+', // variable
        self::PATTERN_LABEL, // label
        '"' . self::PATTERN_LABEL . '"',
        '\'' . self::PATTERN_LABEL . '\'',
        '[0-9]+\.[0-9]+',
        '[0-9]+',
    ];
    private const TOKEN_VALUE_MAP = [
        ']' => Token::T_BRACKET_SQUARE_CLOSE,
        '[' => Token::T_BRACKET_SQUARE_OPEN,
        '>' => Token::T_BRACKET_ANGLE_CLOSE,
        '<' => Token::T_BRACKET_ANGLE_OPEN,
        '{' => Token::T_BRACKET_CURLY_OPEN,
        '}' => Token::T_BRACKET_CURLY_CLOSE,
        '(' => Token::T_PAREN_OPEN,
        ')' => Token::T_PAREN_CLOSE,
        ',' => Token::T_COMMA,
        '[]' => Token::T_LIST,
        '?' => Token::T_NULLABLE,
        '|' => Token::T_BAR,
        '&' => Token::T_AMPERSAND,
        '=' => Token::T_EQUALS,
        ':' => Token::T_COLON,
        '::' => Token::T_DOUBLE_COLON,
        '!' => Token::T_BANG,
    ];

    /**
     * @var string[]
     */
    private const IGNORE_PATTERNS = [
        '\s+',
    ];

    private readonly string $pattern;

    public function __construct()
    {
        $this->pattern = sprintf(
            '{(%s)|%s}',
            implode(')|(', self::PATTERNS),
            implode('|', self::IGNORE_PATTERNS)
        );
    }

    public function lex(string $docblock): Tokens
    {
        $chunks = preg_split(
            $this->pattern,
            $docblock,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE
        );

        if (false === $chunks) {
            throw new RuntimeException(
                'Unexpected error from preg_split'
            );
        }


        $tokens = [];
        foreach ((array)$chunks as $chunk) {
            [ $value, $offset ] = $chunk;
            $tokens[] = new Token(
                $offset,
                $this->resolveType($value),
                $value
            );
        }

        return new Tokens($tokens);
    }

    private function resolveType(string $value): string
    {
        if (str_contains($value, '/*')) {
            return Token::T_PHPDOC_OPEN;
        }

        if (str_contains($value, '*/')) {
            return Token::T_PHPDOC_CLOSE;
        }

        if (trim($value) === '*') {
            return Token::T_ASTERISK;
        }

        if (array_key_exists($value, self::TOKEN_VALUE_MAP)) {
            return self::TOKEN_VALUE_MAP[$value];
        }

        if (is_numeric($value)) {
            if (str_contains($value, '.')) {
                return Token::T_FLOAT;
            }
            return Token::T_INTEGER;
        }

        if ($value[0] === '"' || $value[0] == '\'') {
            return Token::T_QUOTED_STRING;
        }

        if ($value[0] === '$') {
            return Token::T_VARIABLE;
        }

        if ($value[0] === '@') {
            return Token::T_TAG;
        }
        if (trim($value) === '') {
            return Token::T_WHITESPACE;
        }

        if (ctype_alpha($value[0]) || $value[0] === '\\') {
            return Token::T_LABEL;
        }

        return Token::T_UNKNOWN;
    }
}
