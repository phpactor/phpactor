<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Tests\Provider;

use Amp\NullCancellationToken;
use Generator;
use Phpactor\Diff\RangesForDiff;
use Phpactor\Extension\LanguageServerPhpCsFixer\Provider\PhpCsFixerDiagnosticsProvider;
use Phpactor\Extension\LanguageServerPhpCsFixer\Tests\PhpCsFixerTestCase;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Psr\Log\NullLogger;
use function Amp\Promise\wait;

class PhpCsFixerDiagnosticsProviderTest extends PhpCsFixerTestCase
{
    /**
     * @dataProvider fileProvider
     */
    public function testProvideDiagnosticsVisible(string $fileContent, int $expectedDiagnostics): void
    {
        $provider = $this->getPhpCsFixerDiagnosticsProvider(true);

        $cancel = new NullCancellationToken();
        $document = ProtocolFactory::textDocumentItem('/tmp/test.php', $fileContent);

        $diagnostics = wait($provider->provideDiagnostics($document, $cancel));
        self::assertIsArray($diagnostics);
        foreach ($diagnostics as $diagnostic) {
            self::assertInstanceOf(Diagnostic::class, $diagnostic);
        }
        self::assertCount($expectedDiagnostics, $diagnostics);
    }

    /**
     * @dataProvider fileProvider
     */
    public function testProvideDiagnosticsHidden(string $fileContent): void
    {
        $provider = $this->getPhpCsFixerDiagnosticsProvider(false);

        $cancel = new NullCancellationToken();
        $document = ProtocolFactory::textDocumentItem('/tmp/test.php', $fileContent);

        $diagnostics = wait($provider->provideDiagnostics($document, $cancel));
        self::assertIsArray($diagnostics);
        self::assertCount(0, $diagnostics);
    }

    /**
     * @dataProvider fileProvider
     */
    public function testProvideActionsForVisibleDiagnostics(string $fileContent, int $expectedDiagnostics): void
    {
        $provider = $this->getPhpCsFixerDiagnosticsProvider(true);

        $cancel = new NullCancellationToken();
        $document = ProtocolFactory::textDocumentItem('/tmp/test.php', $fileContent);

        $actions = wait(
            $provider->provideActionsFor(
                $document,
                new Range(
                    new Position(0, 0),
                    new Position(PHP_INT_MAX, PHP_INT_MAX)
                ),
                $cancel
            )
        );

        self::assertIsArray($actions);
        if ($expectedDiagnostics > 0) {
            self::assertTrue(count($actions) > 0, 'Expected at least one action if file has diagnostics');
        }
        foreach ($actions as $action) {
            self::assertInstanceOf(CodeAction::class, $action);
        }
    }

    /**
     * @dataProvider fileProvider
     */
    public function testProvideActionsForHiddenDiagnostics(string $fileContent, int $expectedDiagnostics): void
    {
        $provider = $this->getPhpCsFixerDiagnosticsProvider(false);

        $cancel = new NullCancellationToken();
        $document = ProtocolFactory::textDocumentItem('/tmp/test.php', $fileContent);

        $actions = wait(
            $provider->provideActionsFor(
                $document,
                new Range(
                    new Position(0, 0),
                    new Position(PHP_INT_MAX, PHP_INT_MAX)
                ),
                $cancel
            )
        );

        self::assertIsArray($actions);
        if ($expectedDiagnostics > 0) {
            self::assertTrue(count($actions) > 0, 'Expected at least one action if file has diagnostics');
        }
        foreach ($actions as $action) {
            self::assertInstanceOf(CodeAction::class, $action);
        }
    }

    public function getPhpCsFixerDiagnosticsProvider(bool $showDiagnostics): PhpCsFixerDiagnosticsProvider
    {
        $phpCsFixer = $this->getPhpCsFixer();

        return new PhpCsFixerDiagnosticsProvider(
            $phpCsFixer,
            new RangesForDiff(),
            $showDiagnostics,
            new NullLogger()
        );
    }

    /**
     * @return Generator<array{string, int}>
     */
    public function fileProvider(): Generator
    {
        yield [
            <<<EOF
                <?php

                namespace Test;
                \$foo = 'bar';
                    \$test1 = true;
                    \$test2 = true;
                    \$test3 = true;
                \$lao = "tzu";
                EOF,
            // expected diagnostics
            4,
        ];
        yield [
            <<<EOF
                <?php

                \$foo = 'bar';

                EOF,
            0
        ];
    }
}
