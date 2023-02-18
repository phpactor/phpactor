<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\Command;

use Phpactor\Extension\LanguageServer\Tests\Unit\LanguageServerTestCase;
use Symfony\Component\Process\Process;

class DiagnosticsCommandTest extends LanguageServerTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testDiagnostics(): void
    {
        $diagnostics = $this->diagnosticsFor('<?php ');
        dump($diagnostics);
    }

    private function diagnosticsFor(string $sourceCode): string
    {
        $process = new Process([
            __DIR__ . '/../../../../../../bin/phpactor',
            'language-server:diagnostics',
        ], $this->workspace()->path(), [], $sourceCode);
        $process->mustRun();
        return $process->getOutput();
    }
}
