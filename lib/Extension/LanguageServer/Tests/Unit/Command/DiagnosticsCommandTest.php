<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\Command;

use Phpactor\Extension\LanguageServer\Tests\Unit\LanguageServerTestCase;
use Phpactor\LanguageServerProtocol\Diagnostic;
use RuntimeException;
use Symfony\Component\Process\Process;

class DiagnosticsCommandTest extends LanguageServerTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testDiagnostics(): void
    {
        $diagnostics = $this->diagnosticsFor('<?php Barfoo::class; // class not found');
        self::assertCount(1, $diagnostics);
        self::assertEquals('Class "Barfoo" not found', $diagnostics[0]->message);
    }

    /**
     * @return Diagnostic[]
     */
    private function diagnosticsFor(string $sourceCode): array
    {
        $process = new Process([
            __DIR__ . '/../../../../../../bin/phpactor',
            'language-server:diagnostics',
        ], $this->workspace()->path(), [], $sourceCode);
        $process->mustRun();

        $diagnostics = array_map(function (mixed $diagnostic): Diagnostic {
            if (!is_array($diagnostic)) {
                throw new RuntimeException('nope');
            }
            return Diagnostic::fromArray($diagnostic);
        }, (array)json_decode($process->getOutput(), true));

        return $diagnostics;
    }
}
