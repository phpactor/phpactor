<?php

namespace Phpactor\Extension\LanguageServerMago\Tests\Model;

use Amp\NullCancellationToken;
use PHPUnit\Framework\Attributes\Group;
use Phpactor\Extension\LanguageServerMago\Model\MagoConfig;
use Phpactor\Extension\LanguageServerMago\Model\MagoProcess;
use Phpactor\Extension\LanguageServerMago\Tests\IntegrationTestCase;
use Psr\Log\NullLogger;
use function Amp\Promise\wait;

/**
 * Smoke test against the real Mago binary. It self-skips when `mago` is not on
 * the PATH, so it is a no-op in the default suite. The `language-server-mago`
 * group lets CI run it deliberately (`phpunit --group language-server-mago`)
 * after installing the binary, e.g. composer require carthage-software/mago.
 */
#[Group('language-server-mago')]
class MagoRealBinaryTest extends IntegrationTestCase
{
    private string $mago = '';

    protected function setUp(): void
    {
        $mago = trim((string)shell_exec('command -v mago'));
        if ($mago === '' || !is_executable($mago)) {
            $this->markTestSkipped('mago binary is not available on PATH');
        }
        $this->mago = $mago;
        $this->workspace()->reset();
        $this->workspace()->put('mago.toml', "[source]\npaths = [\"src\"]\n");
    }

    public function testAnalyzeReportsTypeErrorsFromTheRealBinary(): void
    {
        $text = <<<'PHP'
            <?php

            function add(int $a): int
            {
                return $a;
            }

            add('not an int');
            PHP;
        $this->workspace()->put('src/A.php', $text);

        $process = new MagoProcess(
            $this->workspace()->path(),
            new MagoConfig($this->mago, 20000, null),
            new NullLogger(),
        );

        $diagnostics = wait($process->analyse(
            'analyze',
            'mago',
            'src/A.php',
            'file://' . $this->workspace()->path('src/A.php'),
            $text,
            new NullCancellationToken(),
        ));

        self::assertNotEmpty($diagnostics);
        self::assertSame('mago', $diagnostics[0]->source);
    }
}
