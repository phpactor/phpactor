<?php

namespace Phpactor\Extension\LanguageServerCompletion\Tests\Unit\Util;

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

    public function dataProvider(): array
    {
        return [
            [Suggestion::TYPE_VARIABLE, '$foo', 'foo'],
            [Suggestion::TYPE_FUNCTION, 'foo', 'foo'],
            [Suggestion::TYPE_FIELD, 'foo', 'foo'],
        ];
    }
}
