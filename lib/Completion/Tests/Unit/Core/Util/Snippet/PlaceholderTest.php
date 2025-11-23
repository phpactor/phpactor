<?php

namespace Phpactor\Completion\Tests\Unit\Core\Util\Snippet;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Core\Util\Snippet\Placeholder;

final class PlaceholderTest extends TestCase
{
    #[DataProvider('providePlaceholders')]
    public function testRaw(int $position, ?string $text): void
    {
        $this->assertEquals(
            \sprintf('${%d%s}', $position, $text ? ":$text" : null),
            Placeholder::raw($position, $text)
        );
    }

    #[DataProvider('providePlaceholders')]
    public function testEscape(int $position, ?string $text, string $expected): void
    {
        $this->assertEquals(
            $expected,
            Placeholder::escape($position, $text)
        );
    }
    /**
     * @return Generator<string,array{int,string|null,string}>
     */
    public static function providePlaceholders(): Generator
    {
        yield 'no text' => [1, null, '${1}'];
        yield 'with text' => [3, 'default', '${3:default}'];
        yield 'with a $' => [3, '$default', '${3:\$default}'];
        yield 'with a \\' => [3, '\default', '${3:\\\default}'];
        yield 'with a }' => [3, 'default}', '${3:default\}}'];
    }
}
