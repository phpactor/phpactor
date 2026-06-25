<?php

namespace Phpactor\Extension\LanguageServerMago\Tests\Model\Linter;

use Amp\NullCancellationToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerMago\Model\Linter\MagoLinter;
use Phpactor\Extension\LanguageServerMago\Model\MagoConfig;
use Phpactor\Extension\LanguageServerMago\Model\MagoProcess;
use Psr\Log\NullLogger;
use function Amp\Promise\wait;

class MagoLinterTest extends TestCase
{
    private const ROOT = '/home/project';

    #[DataProvider('provideUncontainedDocuments')]
    public function testReturnsNoDiagnosticsForUncontainedDocuments(string $url): void
    {
        // The process binary does not exist; these cases must return before it
        // is ever invoked.
        $linter = new MagoLinter(
            new MagoProcess(self::ROOT, new MagoConfig('/does/not/exist/mago', 1000, null), new NullLogger()),
            self::ROOT,
            'analyze',
            'mago',
        );

        self::assertSame([], wait($linter->lint($url, '<?php', new NullCancellationToken())));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideUncontainedDocuments(): iterable
    {
        yield 'non-file scheme' => ['untitled:Untitled-1'];
        yield 'outside the root' => ['file:///home/other/file.php'];
        yield 'sibling whose name prefixes the root' => ['file:///home/project2/file.php'];
        yield 'the root itself' => ['file://' . self::ROOT];
        yield 'parent escape' => ['file:///home/project/../escape.php'];
    }
}
