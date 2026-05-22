<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\Command;

use Phpactor\Extension\LanguageServer\Tests\Unit\LanguageServerTestCase;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\CodeActionContext;
use Phpactor\LanguageServerProtocol\CodeActionParams;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use RuntimeException;
use Symfony\Component\Process\Process;

class CodeActionsCommandTest extends LanguageServerTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testDiagnostics(): void
    {
        $actions = $this->codeActionsFor('<?php class Foobar { public function bar() { return "asd"; } }');
        self::assertGreaterThanOrEqual(1, $actions);
    }

    /**
     * @return CodeAction[]
     */
    private function codeActionsFor(string $sourceCode): array
    {
        $process = new Process([
            PHP_BINARY,
            __DIR__ . '/../../../../../../bin/phpactor',
            'language-server:code-actions',
            $payload= json_encode(new CodeActionParams(
                ProtocolFactory::textDocumentIdentifier('file:///foobar'),
                ProtocolFactory::range(0, 0, 100, 100),
                new CodeActionContext([]),
            )),
        ], $this->workspace()->path(), [], $sourceCode);
        $process->mustRun();

        $actions = array_map(function (mixed $array): CodeAction {
            if (!is_array($array)) {
                throw new RuntimeException('nope');
            }
            return CodeAction::fromArray($array);
        }, (array)json_decode($process->getOutput(), true));

        return $actions;
    }
}
