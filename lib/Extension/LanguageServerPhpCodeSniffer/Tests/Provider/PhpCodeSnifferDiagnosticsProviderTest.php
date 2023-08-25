<?php

namespace Phpactor\Extension\LanguageServerPhpCodeSniffer\Tests\Provider;

use Amp\NullCancellationToken;
use Phpactor\Diff\RangesForDiff;
use Phpactor\Extension\LanguageServerPhpCodeSniffer\Provider\PhpCodeSnifferDiagnosticsProvider;
use Phpactor\Extension\LanguageServerPhpCodeSniffer\Tests\PhpCodeSnifferTestCase;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Psr\Log\NullLogger;
use function Amp\Promise\wait;

class PhpCodeSnifferDiagnosticsProviderTest extends PhpCodeSnifferTestCase
{

    /**
     * @dataProvider fileProvider
     */
    public function testProvideDiagnosticsVisible(string $fileContent, int $expectedDiagnostics): void
    {
        $provider = $this->getPhpCodeSnifferDiagnosticsProvider(true);

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
        $provider = $this->getPhpCodeSnifferDiagnosticsProvider(false);

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
        $provider = $this->getPhpCodeSnifferDiagnosticsProvider(true);

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
        $provider = $this->getPhpCodeSnifferDiagnosticsProvider(false);

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

    public function getPhpCodeSnifferDiagnosticsProvider(bool $showDiagnostics): PhpCodeSnifferDiagnosticsProvider
    {
        $phpCodeSniffer = $this->getPhpCodeSniffer();

        return new PhpCodeSnifferDiagnosticsProvider(
            $phpCodeSniffer,
            $showDiagnostics,
            new RangesForDiff(),
            new NullLogger()
        );
    }

    /**
     * @return array{0: string, 1:int}[]
     */
    public function fileProvider(): array
    {
        return [
            'PEAR: tab indentation' => [
                <<<EOF
                    <?php

                    namespace Test;
                    \$foo = 'bar';
                     	\$test1 = true; // tab indent
                      \$test2 = true;
                        \$test3 = true;
                    \$lao = "tzu";
                    EOF,
                // expected diagnostics
                1,
            ],
            'PEAR: correct file' => [
                <<<EOF
                    <?php

                    /**
                     * Php version: 8.0
                     *
                     * File comment
                     *
                     * @category Category
                     * @package  Package
                     * @author   Firstname Lastname <3f5eY@example.com>
                     * @license  https://mit-license.org/ MIT
                     * @link     Link
                     **/

                    \$foo = 'bar';

                    EOF,
                0
            ]
        ];
    }
}
