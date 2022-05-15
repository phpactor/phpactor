<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phpactor\Extension\Behat\Behat\Pattern;

/**
 * Defines a way to handle turnip patterns.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TurnipPatternPolicy implements PatternPolicy
{
    public const TOKEN_REGEX = "[\"']?(?P<%s>(?<=\")[^\"]*(?=\")|(?<=')[^']*(?=')|\-?[\w\.\,]+)['\"]?";
    public const PLACEHOLDER_REGEXP = "/\\\:(\w+)/";
    public const OPTIONAL_WORD_REGEXP = '/(\s)?\\\\\(([^\\\]+)\\\\\)(\s)?/';
    public const ALTERNATIVE_WORD_REGEXP = '/(\w+)\\\\\/(\w+)/';

    /**
     * @var string[]
     */
    private array $regexCache = [];

    public function transformPatternToRegex($pattern): string
    {
        if (!isset($this->regexCache[$pattern])) {
            $this->regexCache[$pattern] = $this->createTransformedRegex($pattern);
        }
        return $this->regexCache[$pattern];
    }

    /**
     * @param string $pattern
     */
    private function createTransformedRegex($pattern): string
    {
        $regex = preg_quote($pattern, '/');

        $regex = $this->replaceTokensWithRegexCaptureGroups($regex);
        $regex = $this->replaceTurnipOptionalEndingWithRegex($regex);
        $regex = $this->replaceTurnipAlternativeWordsWithRegex($regex);

        return '/^' . $regex . '$/iu';
    }

    /**
     * Replaces turnip tokens with regex capture groups.
     */
    private function replaceTokensWithRegexCaptureGroups(string $regex): string
    {
        $tokenRegex = self::TOKEN_REGEX;

        return preg_replace_callback(
            self::PLACEHOLDER_REGEXP,
            [$this, 'replaceTokenWithRegexCaptureGroup'],
            $regex
        );
    }

    /**
     * @param string[] $tokenMatch
     */
    private function replaceTokenWithRegexCaptureGroup(array $tokenMatch): string
    {
        if (strlen($tokenMatch[1]) >= 32) {
            throw new InvalidPatternException(
                "Token name should not exceed 32 characters, but `{$tokenMatch[1]}` was used."
            );
        }

        return sprintf(self::TOKEN_REGEX, $tokenMatch[1]);
    }

    /**
     * Replaces turnip optional ending with regex non-capturing optional group.
     */
    private function replaceTurnipOptionalEndingWithRegex(string $regex): string
    {
        return preg_replace(self::OPTIONAL_WORD_REGEXP, '(?:\1)?(?:\2)?(?:\3)?', $regex);
    }

    /**
     * Replaces turnip alternative words with regex non-capturing alternating group.
     */
    private function replaceTurnipAlternativeWordsWithRegex(string $regex): string
    {
        $regex = preg_replace(self::ALTERNATIVE_WORD_REGEXP, '(?:\1|\2)', $regex);
        $regex = $this->removeEscapingOfAlternationSyntax($regex);

        return $regex;
    }

    /**
     * Removes escaping of alternation syntax from regex.
     *
     * This method removes those escaping backslashes from your slashes, so your steps
     * could be matched against your escaped definitions.
     */
    private function removeEscapingOfAlternationSyntax(string $regex): string
    {
        return str_replace('\\\/', '/', $regex);
    }
}
