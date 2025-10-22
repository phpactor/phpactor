<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Tests\Unit\DiagnosticProvider;

use Amp\CancellationTokenSource;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerWorseReflection\DiagnosticProvider\WorseDiagnosticProvider;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity as PhpactorDiagnosticSeverity;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\DiagnosticProvider\BareDiagnostic;
use Phpactor\WorseReflection\Core\DiagnosticProvider\InMemoryDiagnosticProvider;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\ReflectorBuilder;
use function Amp\Promise\wait;

class WorseDiagnosticProviderTest extends TestCase
{
    public function testDiagnostics(): void
    {
        $reflector = ReflectorBuilder::create()->addDiagnosticProvider(new InMemoryDiagnosticProvider([
            new BareDiagnostic(ByteOffsetRange::fromInts(1, 1), DiagnosticSeverity::WARNING(), 'Foo', 'foo')
        ]))->build();

        $cancel = (new CancellationTokenSource())->getToken();
        $lspDiagnostics = wait((
            new WorseDiagnosticProvider($reflector)
        )->provideDiagnostics(
            ProtocolFactory::textDocumentItem('file:///foo', 'foo'),
            $cancel
        ));

        /** @var Diagnostic[] $lspDiagnostics */
        self::assertCount(1, $lspDiagnostics);
        self::assertInstanceOf(Diagnostic::class, $lspDiagnostics[0]);
        self::assertEquals('Foo', $lspDiagnostics[0]->message);
        self::assertEquals('worse.foo', $lspDiagnostics[0]->code);
        self::assertEquals(PhpactorDiagnosticSeverity::WARNING, $lspDiagnostics[0]->severity);
    }
}
