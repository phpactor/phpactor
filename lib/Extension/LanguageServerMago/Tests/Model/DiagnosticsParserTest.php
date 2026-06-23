<?php

namespace Phpactor\Extension\LanguageServerMago\Tests\Model;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerMago\Model\DiagnosticsParser;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use RuntimeException;

class DiagnosticsParserTest extends TestCase
{
    private const URI = 'file:///src/A.php';
    private const REL = 'src/A.php';

    // The source the analyze.json/lint.json fixtures were captured against, so
    // the recorded byte offsets line up.
    private const EXAMPLE_SOURCE = <<<'PHP'
        <?php

        function add(int $a, int $b): int
        {
            return $a + $b;
        }

        add('x', 'y');
        PHP;

    public function testParsesIssueWithRangeSeverityCodeAndRelatedInformation(): void
    {
        $text = "<?php\n\$x = foo();\n";
        $json = json_encode([
            'issues' => [
                [
                    'level' => 'Error',
                    'code' => 'undefined-function',
                    'message' => 'Function foo not found',
                    'notes' => ['It is not defined anywhere'],
                    'help' => 'Did you mean bar?',
                    'annotations' => [
                        [
                            'message' => 'called here',
                            'kind' => 'Primary',
                            'span' => $this->span(self::REL, 11, 16),
                        ],
                        [
                            'message' => 'assigned here',
                            'kind' => 'Secondary',
                            'span' => $this->span(self::REL, 6, 8),
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $diagnostics = (new DiagnosticsParser())->parse($json, $text, 'mago', self::REL, self::URI);

        self::assertCount(1, $diagnostics);
        $diagnostic = $diagnostics[0];
        self::assertEquals(new Range(new Position(1, 5), new Position(1, 10)), $diagnostic->range);
        self::assertSame(DiagnosticSeverity::ERROR, $diagnostic->severity);
        self::assertSame('undefined-function', $diagnostic->code);
        self::assertSame('mago', $diagnostic->source);
        self::assertStringContainsString('Function foo not found', $diagnostic->message);
        self::assertStringContainsString('It is not defined anywhere', $diagnostic->message);
        self::assertStringContainsString('Did you mean bar?', $diagnostic->message);

        self::assertNotNull($diagnostic->relatedInformation);
        self::assertCount(1, $diagnostic->relatedInformation);
        $related = $diagnostic->relatedInformation[0];
        self::assertSame('assigned here', $related->message);
        self::assertSame(self::URI, $related->location->uri);
        self::assertEquals(new Range(new Position(1, 0), new Position(1, 2)), $related->location->range);
    }

    public function testDropsIssueWhosePrimaryAnnotationIsInAnotherFile(): void
    {
        $json = json_encode([
            'issues' => [
                [
                    'level' => 'Error',
                    'code' => 'x',
                    'message' => 'in another file',
                    'annotations' => [
                        ['kind' => 'Primary', 'span' => $this->span('other/B.php', 0, 1)],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $diagnostics = (new DiagnosticsParser())->parse($json, "<?php\n", 'mago', self::REL, self::URI);

        self::assertCount(0, $diagnostics);
    }

    public function testByteOffsetMapsToUtf16Column(): void
    {
        // The closing quote sits at byte offset 17 but UTF-16 column 10, because
        // "é" is two UTF-8 bytes but one UTF-16 code unit.
        $text = "<?php\n\$x = 'café';\n";
        $json = json_encode([
            'issues' => [
                [
                    'level' => 'Warning',
                    'code' => 'x',
                    'message' => 'm',
                    'annotations' => [
                        ['kind' => 'Primary', 'span' => $this->span(self::REL, 17, 18)],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $diagnostics = (new DiagnosticsParser())->parse($json, $text, 'mago-lint', self::REL, self::URI);

        self::assertCount(1, $diagnostics);
        self::assertSame(DiagnosticSeverity::WARNING, $diagnostics[0]->severity);
        self::assertSame(1, $diagnostics[0]->range->start->line);
        self::assertSame(10, $diagnostics[0]->range->start->character);
    }

    public function testThrowsOnInvalidJson(): void
    {
        $this->expectException(RuntimeException::class);
        (new DiagnosticsParser())->parse('not json', '', 'mago', self::REL, self::URI);
    }

    public function testParsesRealAnalyzeFixture(): void
    {
        $diagnostics = (new DiagnosticsParser())->parse(
            $this->fixture('analyze.json'),
            self::EXAMPLE_SOURCE,
            'mago',
            'src/Example.php',
            'file:///src/Example.php',
        );

        self::assertCount(2, $diagnostics);
        foreach ($diagnostics as $diagnostic) {
            self::assertSame(DiagnosticSeverity::ERROR, $diagnostic->severity);
            self::assertSame('invalid-argument', $diagnostic->code);
            self::assertSame('mago', $diagnostic->source);
        }
    }

    public function testParsesRealLintFixture(): void
    {
        $diagnostics = (new DiagnosticsParser())->parse(
            $this->fixture('lint.json'),
            self::EXAMPLE_SOURCE,
            'mago-lint',
            'src/Example.php',
            'file:///src/Example.php',
        );

        self::assertCount(2, $diagnostics);
        foreach ($diagnostics as $diagnostic) {
            self::assertSame(DiagnosticSeverity::WARNING, $diagnostic->severity);
            self::assertSame('mago-lint', $diagnostic->source);
        }
    }

    /**
     * @return array{file_id: array{name: string}, start: array{offset: int, line: int}, end: array{offset: int, line: int}}
     */
    private function span(string $name, int $start, int $end): array
    {
        return [
            'file_id' => ['name' => $name],
            'start' => ['offset' => $start, 'line' => 0],
            'end' => ['offset' => $end, 'line' => 0],
        ];
    }

    private function fixture(string $name): string
    {
        return (string)file_get_contents(__DIR__ . '/../Fixtures/' . $name);
    }
}
