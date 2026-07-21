<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\DiagnosticsProvider;

use Amp\CancellationTokenSource;
use Phpactor\Extension\LanguageServer\DiagnosticProvider\OutsourcedDiagnosticsProvider;
use Phpactor\Extension\LanguageServer\Tests\Unit\LanguageServerTestCase;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Psr\Log\NullLogger;
use function Amp\Promise\wait;

class OutsourcedDiagnosticsProvierTest extends LanguageServerTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testDiagnostics(): void
    {
        $provider = new OutsourcedDiagnosticsProvider([
            __DIR__ . '/../../../../../../bin/phpactor',
            'language-server:diagnostics',
        ], $this->workspace()->path(), new NullLogger());
        $diagnostics = wait($provider->provideDiagnostics(
            ProtocolFactory::textDocumentItem('file:///foo', '<?php echo Hello::class'),
            (new CancellationTokenSource())->getToken()
        ));
        self::assertCount(1, $diagnostics);
        self::assertEquals('Class "Hello" not found', $diagnostics[0]->message);
    }

    public function testAlreadyCancelledDiagnostics(): void
    {
        $provider = new OutsourcedDiagnosticsProvider([
            __DIR__ . '/../../../../../../bin/phpactor',
            'language-server:diagnostics',
        ], $this->workspace()->path(), new NullLogger());
        $source = (new CancellationTokenSource());
        $source->cancel();
        $diagnostics = wait($provider->provideDiagnostics(
            ProtocolFactory::textDocumentItem('file:///foo', '<?php echo Hello::class'),
            $source->getToken()
        ));
        self::assertCount(0, $diagnostics);
    }
}
