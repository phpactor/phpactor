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
 * Defines a way to handle regex patterns.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RegexPatternPolicy implements PatternPolicy
{
    public function transformPatternToRegex($pattern): string
    {
        if (false === @preg_match($pattern, 'anything')) {
            $error = error_get_last();
            $errorMessage = $error['message'] ?? '';

            throw new InvalidPatternException(sprintf('The regex `%s` is invalid: %s', $pattern, $errorMessage));
        }

        return $pattern;
    }
}
