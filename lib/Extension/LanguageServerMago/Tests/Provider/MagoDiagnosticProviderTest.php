<?php

namespace Phpactor\Extension\LanguageServerMago\Tests\Provider;

use Amp\NullCancellationToken;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerMago\Model\Linter\TestLinter;
use Phpactor\Extension\LanguageServerMago\Provider\MagoDiagnosticProvider;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use function Amp\Promise\wait;

class MagoDiagnosticProviderTest extends TestCase
{
    public function testDelegatesToLinterWhenEnabled(): void
    {
        $diagnostic = $this->diagnostic();
        $provider = new MagoDiagnosticProvider(new TestLinter([$diagnostic]), 'mago', true);

        $result = wait($provider->provideDiagnostics($this->document(), new NullCancellationToken()));

        self::assertSame([$diagnostic], $result);
        self::assertSame('mago', $provider->name());
    }

    public function testReturnsNothingWhenDisabled(): void
    {
        $provider = new MagoDiagnosticProvider(new TestLinter([$this->diagnostic()]), 'mago-lint', false);

        $result = wait($provider->provideDiagnostics($this->document(), new NullCancellationToken()));

        self::assertSame([], $result);
        self::assertSame('mago-lint', $provider->name());
    }

    private function diagnostic(): Diagnostic
    {
        return new Diagnostic(
            range: new Range(new Position(0, 0), new Position(0, 1)),
            message: 'something',
            source: 'mago',
        );
    }

    private function document(): TextDocumentItem
    {
        return new TextDocumentItem('file:///src/A.php', 'php', 1, '<?php');
    }
}
