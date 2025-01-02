<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\DiagnosticsProvider;

use Amp\CancellationTokenSource;
use Amp\Success;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServer\DiagnosticProvider\CodeFilteringDiagnosticProvider;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServer\Core\Diagnostics\ClosureDiagnosticsProvider;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use function Amp\Promise\wait;

class CodeFilteringDiagnosticProviderTest extends TestCase
{
    public function testFilter(): void
    {
        $provider = new ClosureDiagnosticsProvider(function () {
            return new Success([
                new Diagnostic(
                    range: ProtocolFactory::range(0, 0, 0, 0),
                    message: 'foobar',
                    code: 'foo',
                ),
                new Diagnostic(
                    range: ProtocolFactory::range(0, 0, 0, 0),
                    message: 'foobar',
                    code: 'bar',
                ),
            ]);
        });

        $provider = new CodeFilteringDiagnosticProvider($provider, ['bar']);
        $diagnostics = wait($provider->provideDiagnostics(
            ProtocolFactory::textDocumentItem('file:///foo', ''),
            (new CancellationTokenSource())->getToken()
        ));
        self::assertCount(1, $diagnostics);
        $diagnostic = $diagnostics[0] ?? null;
        self::assertInstanceOf(Diagnostic::class, $diagnostic);
        self::assertEquals('foo', $diagnostic->code);
    }
}
