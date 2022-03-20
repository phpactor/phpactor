<?php

namespace Phpactor\DocblockParser\Tests\Benchmark;

use Generator;
use Phpactor\DocblockParser\Lexer;

class LexerBench
{
    /**
     * @ParamProviders({"provideDocblock"})
     */
    public function benchLex(array $docblock): void
    {
        $t = (new Lexer())->lex($docblock['docblock']);
    }

    public function provideDocblock(): Generator
    {
        yield [
            'docblock' => <<<'EOT'
                /**
                 * This is some complicated method
                 * @since 5.2
                 *
                 * @param Foobar $barfoo Does a barfoo and then returns
                 * @param Barfoo $foobar Performs a foobar and then runs away.
                 *
                 * @return Baz
                 */
                EOT
        ];
        yield [
            'docblock' => <<<'EOT'
                /**
                 * Assert library.
                 *
                 * @author Benjamin Eberlei <kontakt@beberlei.de>
                 *
                 * @method static bool allAlnum(mixed $value, string|callable $message = null, string $propertyPath = null) Assert that value is alphanumeric for all values.
                 * @method static bool allBase64(string $value, string|callable $message = null, string $propertyPath = null) Assert that a constant is defined for all values.
                 * @method static bool allBetween(mixed $value, mixed $lowerLimit, mixed $upperLimit, string|callable $message = null, string $propertyPath = null) Assert that a value is greater or equal than a lower limit, and less than or equal to an upper limit for all values.
                 * @method static bool allBetweenExclusive(mixed $value, mixed $lowerLimit, mixed $upperLimit, string|callable $message = null, string $propertyPath = null) Assert that a value is greater than a lower limit, and less than an upper limit for all values.
                 * @method static bool allBetweenLength(mixed $value, int $minLength, int $maxLength, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8') Assert that string length is between min and max lengths for all values.
                 * @method static bool allBoolean(mixed $value, string|callable $message = null, string $propertyPath = null) Assert that value is php boolean for all values.
                 * @method static bool allChoice(mixed $value, array $choices, string|callable $message = null, string $propertyPath = null) Assert that value is in array of choices for all values.
                 * @method static bool allChoicesNotEmpty(array $values, array $choices, string|callable $message = null, string $propertyPath = null) Determines if the values array has every choice as key and that this choice has content for all values.
                 * @method static bool allClassExists(mixed $value, string|callable $message = null, string $propertyPath = null) Assert that the class exists for all values.
                 * @method static bool allContains(mixed $string, string $needle, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8') Assert that string contains a sequence of chars for all values.
                 * @method static bool allCount(array|Countable|ResourceBundle|SimpleXMLElement $countable, int $count, string|callable $message = null, string $propertyPath = null) Assert that the count of countable is equal to count for all values.
                 * @method static bool allDate(string $value, string $format, string|callable $message = null, string $propertyPath = null) Assert that date is valid and corresponds to the given format for all values.
                EOT
        ];
    }
}
