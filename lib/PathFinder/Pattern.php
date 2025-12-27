<?php

namespace Phpactor\PathFinder;

use Phpactor\PathFinder\Exception\NoPlaceHoldersException;
use RuntimeException;
use Symfony\Component\Filesystem\Path;

class Pattern
{
    const TOKEN_REGEX = '{<([a-z-]+?)>}';

    /**
     * @param array<string> $tokenNames
     */
    public function __construct(
        private readonly string $regex,
        private readonly string $pattern,
        private readonly array $tokenNames
    ) {
    }

    public static function fromPattern(string $pattern): self
    {
        preg_match_all(self::TOKEN_REGEX, $pattern, $matches);

        [$tokens, $tokenNames] = $matches;

        $regex = $pattern;
        foreach (array_values($matches[0]) as $index => $token) {
            $greedy = $index + 1 !== count($tokenNames);
            $regex = strtr($regex, [$token => sprintf('(?%s%s+)', $token, $greedy ? '[^/]' : '.')]);
        }

        if (empty($tokenNames)) {
            throw new NoPlaceHoldersException(sprintf(
                'File pattern "%s" does not contain any <placeholders>',
                $pattern
            ));
        }

        return new self(sprintf('{%s$}', $regex), $pattern, $tokenNames);
    }

    public function fits(string $filePath): bool
    {
        return (bool)preg_match($this->regex, Path::canonicalize($filePath));
    }

    /**
     * @return array<string, string>
     */
    public function tokens(string $filePath): array
    {
        $filePath = Path::canonicalize($filePath);

        if (!preg_match($this->regex, $filePath, $matches)) {
            throw new RuntimeException(sprintf(
                'Error occurred performing regex on filepath "%s" with regex "%s"',
                $filePath,
                $this->regex
            ));
        }

        return array_intersect_key($matches, (array)array_combine($this->tokenNames, $this->tokenNames));
    }

    /**
     * @param array<string,string> $tokens
     */
    public function replaceTokens(array $tokens): string
    {
        return $this->cleanRemainingTokens($this->replaceTokensWithValues($tokens));
    }

    public function toString(): string
    {
        return $this->pattern;
    }

    /**
     * @param array<string,string> $tokens
     */
    private function replaceTokensWithValues(array $tokens): string
    {
        return strtr($this->pattern, (array)array_combine(array_map(function (string $key) {
            return '<' . $key . '>';
        }, array_keys($tokens)), array_values($tokens)));
    }

    private function cleanRemainingTokens(string $filePath): string
    {
        return strtr($filePath, (array)array_combine(array_map(function (string $tokenName) {
            return '<' . $tokenName . '>';
        }, $this->tokenNames), array_fill(0, count($this->tokenNames), '')));
    }
}
