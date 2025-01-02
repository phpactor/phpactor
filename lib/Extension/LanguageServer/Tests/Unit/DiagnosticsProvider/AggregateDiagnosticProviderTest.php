<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\DiagnosticsProvider;

use Amp\CancellationTokenSource;
use Amp\Success;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServer\DiagnosticProvider\AggregateDiagnosticsProvider;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServer\Core\Diagnostics\ClosureDiagnosticsProvider;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Psr\Log\NullLogger;
use function Amp\Promise\wait;

class AggregateDiagnosticProviderTest extends TestCase
{
    public function testProvidesAggregateDiagnostics(): void
    {
        $providers = [
            $this->createProvider([
                ProtocolFactory::diagnostic(
                    ProtocolFactory::range(1, 1, 2, 2),
                    'one'
                ),
                ProtocolFactory::diagnostic(
                    ProtocolFactory::range(1, 1, 2, 2),
                    'two'
                )
            ]),
            $this->createProvider([
                ProtocolFactory::diagnostic(
                    ProtocolFactory::range(1, 1, 2, 2),
                    'three'
                ),
            ]),
        ];

        $aggregate = $this->createAggregate(...$providers);
        $cancel = (new CancellationTokenSource())->getToken();
        $diagnostics = wait($aggregate->provideDiagnostics(ProtocolFactory::textDocumentItem('file:///', 'text'), $cancel));
        self::assertCount(3, $diagnostics);
        $diagnostic = $diagnostics[0] ?? null;
        self::assertInstanceOf(Diagnostic::class, $diagnostic);
        self::assertEquals('test', $diagnostic->code, 'Uses provider ID as code by default');
    }

    public function testReturnsAggregateName(): void
    {
        $aggregate = $this->createAggregate(...[
            $this->createProvider([], 'one'),
            $this->createProvider([], 'two'),
        ]);
        self::assertEquals('one, two', $aggregate->name());
    }

    private function createAggregate(ClosureDiagnosticsProvider ...$providers): AggregateDiagnosticsProvider
    {
        return new AggregateDiagnosticsProvider(new NullLogger(), ...$providers);
    }

    /**
     * @param Diagnostic[] $diagnostics
     */
    private function createProvider(array $diagnostics, string $name = 'test'): ClosureDiagnosticsProvider
    {
        return new ClosureDiagnosticsProvider(function () use ($diagnostics) {
            return new Success($diagnostics);
        }, $name);
    }
}
