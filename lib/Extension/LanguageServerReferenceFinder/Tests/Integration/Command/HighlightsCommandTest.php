<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Tests\Integration\Command;

use Phpactor\LanguageServerProtocol\DocumentHighlight;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\TestUtils\Workspace;
use RuntimeException;
use Symfony\Component\Process\Process;

class HighlightsCommandTest extends TestCase
{
    private Workspace $workspace;

    public function __construct()
    {
        $this->workspace = new Workspace(__DIR__ . '/../../Workspace');
        parent::__construct();
    }

    protected function setUp(): void
    {
        $this->workspace->reset();
    }

    public function testHighlights(): void
    {
        $highlights = $this->highlightsFor('<?php Barfoo::class; // class not found', 10);
        self::assertCount(1, $highlights);
    }

    /**
     * @return DocumentHighlight[]
     */
    private function highlightsFor(string $sourceCode, int $offset): array
    {
        $process = new Process([
            __DIR__ . '/../../../../../../bin/phpactor',
            'language-server:highlights',
            $offset
        ], $this->workspace->path(), [], $sourceCode);
        $process->mustRun();

        $highlights = array_map(function (mixed $highlight): DocumentHighlight {
            if (!is_array($highlight)) {
                throw new RuntimeException('nope');
            }
            return DocumentHighlight::fromArray($highlight);
        }, (array)json_decode($process->getOutput(), true));

        return $highlights;
    }
}
