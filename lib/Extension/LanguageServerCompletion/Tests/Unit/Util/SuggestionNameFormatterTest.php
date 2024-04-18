<?php

namespace Phpactor\Extension\LanguageServerCompletion\Tests\Unit\Util;

use Generator;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\LanguageServerCompletion\Util\SuggestionNameFormatter;
use PHPUnit\Framework\TestCase;

class SuggestionNameFormatterTest extends TestCase
{
    private SuggestionNameFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new SuggestionNameFormatter(true);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFormat(string $type, string $name, string $expected): void
    {
        $suggestion = Suggestion::createWithOptions($name, ['type' => $type]);

        $this->assertSame($expected, $this->formatter->format($suggestion));
    }

    /**
     * @return Generator<array{string,string,string}>
     */
    public function dataProvider(): Generator
    {
        yield [Suggestion::TYPE_VARIABLE, '$foo', 'foo'];
        yield [Suggestion::TYPE_FUNCTION, 'foo', 'foo'];
        yield [Suggestion::TYPE_FIELD, 'foo', 'foo'];
    }
}
