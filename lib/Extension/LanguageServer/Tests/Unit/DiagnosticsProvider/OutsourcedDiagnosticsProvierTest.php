<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\DiagnosticsProvider;

use Amp\CancellationTokenSource;
use Phpactor\Extension\LanguageServer\DiagnosticProvider\OutsourcedDiagnosticsProvider;
use Phpactor\Extension\LanguageServer\Tests\Unit\LanguageServerTestCase;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use function Amp\Promise\wait;

class OutsourcedDiagnosticsProvierTest extends LanguageServerTestCase
{
    public function testDiagnostics(): void
    {
        $provider = new OutsourcedDiagnosticsProvider([
            __DIR__ . '/../../../../../../bin/phpactor',
            'language-server:diagnostics',
        ]);
        $diagnostics = wait($provider->provideDiagnostics(
            ProtocolFactory::textDocumentItem('file:///foo', '<?php echo Hello::class'),
            (new CancellationTokenSource())->getToken()
        ));
        self::assertCount(1, $diagnostics);
        self::assertEquals('Class "Hello" not found', $diagnostics[0]->message);
    }
}
