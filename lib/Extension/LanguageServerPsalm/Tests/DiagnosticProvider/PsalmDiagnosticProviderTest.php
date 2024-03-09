<?php

namespace Phpactor\Extension\LanguageServerPsalm\Tests\DiagnosticProvider;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPsalm\DiagnosticProvider\PsalmDiagnosticProvider;
use Phpactor\Extension\LanguageServerPsalm\Model\Linter\TestLinter;
use Phpactor\Extension\LanguageServerPsalm\Tests\Util\DiagnosticBuilder;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use function Amp\Promise\wait;
use function Amp\delay;

class PsalmDiagnosticProviderTest extends TestCase
{
    private LanguageServerTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $tester = LanguageServerTesterBuilder::create();
        $tester->addDiagnosticsProvider(new PsalmDiagnosticProvider(
            $this->createTestLinter()
        ));
        $tester->enableDiagnostics();
        $tester->enableTextDocuments();
        $this->tester = $tester->build();
        $this->tester->initialize();
    }

    public function testHandleSingle(): void
    {
        $updated = new TextDocumentUpdated(ProtocolFactory::versionedTextDocumentIdentifier('file:///path', 12), 'asd');
        $this->tester->textDocument()->open('file:///path', 'asd');

        wait(delay(10));

        self::assertEquals(2, $this->tester->transmitter()->count());
    }

    private function createTestLinter(): TestLinter
    {
        return new TestLinter([
            DiagnosticBuilder::create()->build(),
        ], 10);
    }
}
