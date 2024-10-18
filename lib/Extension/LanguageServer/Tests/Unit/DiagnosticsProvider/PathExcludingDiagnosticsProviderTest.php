<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\DiagnosticsProvider;

use Amp\CancellationTokenSource;
use Amp\Success;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServer\DiagnosticProvider\PathExcludingDiagnosticsProvider;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\ClosureDiagnosticsProvider;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use function Amp\Promise\wait;

class PathExcludingDiagnosticsProviderTest extends TestCase
{
    /**
     * @dataProvider provideProvide
     * @param array<int,string> $excludePatterns
     */
    public function testProvide(TextDocumentItem $item, array $excludePatterns, int $expectedCount): void
    {
        $cancel = new CancellationTokenSource();
        $diagnostics = wait((new PathExcludingDiagnosticsProvider(
            new ClosureDiagnosticsProvider(function () {
                return new Success([
                    ProtocolFactory::diagnostic(ProtocolFactory::range(1, 1, 2, 2), 'test'),
                ]);
            }),
            $excludePatterns,
        ))->provideDiagnostics($item, $cancel->getToken()));
        self::assertCount($expectedCount, $diagnostics);
    }
    /**
     * @return Generator<string,array{TextDocumentItem,array<int,string>,int}>
     */
    public function provideProvide(): Generator
    {
        yield 'match pattern' => [
            ProtocolFactory::textDocumentItem(
                'file:///home/daniel/www/foobar/barfoo/vendor/dan/test.php',
                '<?php echo "hello";'
            ),
            [
                '/home/daniel/www/foobar/barfoo/vendor/**/*',
            ],
            0,
        ];
        yield 'no globs, no exclude' => [
            ProtocolFactory::textDocumentItem(
                'file:///home/daniel/www/foobar/barfoo/vendor/dan/test.php',
                '<?php echo "hello";'
            ),
            [
            ],
            1,
        ];
        yield 'non-matching glob' => [
            ProtocolFactory::textDocumentItem(
                'file:///home/daniel/www/foobar/barfoo/vendor/dan/test.php',
                '<?php echo "hello";'
            ),
            [
                '/home/daniel/www/foobar/barfoo/vendor/**/*.xml',
            ],
            1,
        ];
        yield 'non-matching and matching glob' => [
            ProtocolFactory::textDocumentItem(
                'file:///home/daniel/www/foobar/barfoo/vendor/dan/test.php',
                '<?php echo "hello";'
            ),
            [
                '/home/daniel/www/foobar/barfoo/vendor/**/*.xml',
                '/home/daniel/www/foobar/barfoo/vendor/**/*.php',
            ],
            0,
        ];
    }
}
