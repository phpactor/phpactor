<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Tests\Provider;

use Amp\NullCancellationToken;
use Phpactor\Extension\LanguageServerPhpCsFixer\Provider\PhpCsFixerDiagnosticsProvider;
use Phpactor\Extension\LanguageServerPhpCsFixer\Tests\PhpCsFixerTestCase;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Psr\Log\NullLogger;
use function Amp\Promise\wait;

class PhpCsFixerDiagnosticsProviderTest extends PhpCsFixerTestCase
{
    public function testProvideDiagnostics(): void
    {
        $diagnosticsVisibleProvider = $this->getPhpCsFixerDiagnosticsProvider(true);
        $diagnosticsInvisibleProvider = $this->getPhpCsFixerDiagnosticsProvider(false);

        $cancel = new NullCancellationToken();
        $invalidDocument = new TextDocumentItem(
            '/tmp/test.php',
            'php',
            1,
            <<<EOF
                <?php

                namespace Test;
                \$foo = 'bar';
                    \$test1 = true;
                    \$test2 = true;
                    \$test3 = true;
                \$lao = "tzu";
                EOF
        );

        $diagnostics = wait($diagnosticsVisibleProvider->provideDiagnostics($invalidDocument, $cancel));
        $this->assertIsArray($diagnostics);
        if (is_array($diagnostics)) {
            // braces, quotes, blank_line_after_namespace, single_blank_line_at_eof
            $this->assertCount(4, $diagnostics);
        }

        $diagnosticsInvisible = wait($diagnosticsInvisibleProvider->provideDiagnostics($invalidDocument, $cancel));
        $this->assertIsArray($diagnosticsInvisible);
        if (is_array($diagnosticsInvisible)) {
            $this->assertCount(0, $diagnosticsInvisible);
        }

        $validDocument = new TextDocumentItem(
            '/tmp/test.php',
            'php',
            1,
            <<<EOF
                <?php

                \$foo = 'bar';

                EOF
        );

        $diagnostics = wait($diagnosticsVisibleProvider->provideDiagnostics($validDocument, $cancel));
        $this->assertIsArray($diagnostics);
        if (is_array($diagnostics)) {
            $this->assertCount(0, $diagnostics);
        }
    }

    public function testProvideActionsFor(): void
    {
        $provider = $this->getPhpCsFixerDiagnosticsProvider(true);

        $cancel = new NullCancellationToken();
        $invalidDocument = new TextDocumentItem(
            '/tmp/test.php',
            'php',
            1,
            <<<EOF
                <?php

                \$lao = "tzu";

                EOF
        );

        $actions = wait(
            $provider->provideActionsFor(
            $invalidDocument,
            Range::fromArray([
                'start' => Position::fromArray(['line' => 0, 'character' => 0]),
                'end' => Position::fromArray(['line' => 3, 'character' => 0]),
            ]),
            $cancel
        )
        );

        $this->assertIsArray($actions);
        $this->assertInstanceOf(CodeAction::class, $actions[0] ?? null);
    }

    public function getPhpCsFixerDiagnosticsProvider(bool $showDiagnostics): PhpCsFixerDiagnosticsProvider
    {
        $phpCsFixer = $this->getPhpCsFixer();

        return new PhpCsFixerDiagnosticsProvider(
            $phpCsFixer,
            $showDiagnostics,
            new NullLogger()
        );
    }
}
