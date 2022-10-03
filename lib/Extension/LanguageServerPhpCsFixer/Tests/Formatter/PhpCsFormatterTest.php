<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Tests\Formatter;

use function Amp\Promise\wait;
use Phpactor\Extension\LanguageServerPhpCsFixer\Formatter\PhpCsFixerFormatter;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\LanguageServerProtocol\TextEdit;
use PHPUnit\Framework\TestCase;

class PhpCsFormatterTest extends TestCase
{
    public function testHandler(): void
    {
        $edits = $this->format('<?php echo "hello";');
        self::assertCount(1, $edits);

        // arbitrary transformation replaced " with '
        self::assertEquals('<?php echo \'hello\';', trim($edits[0]->newText));
    }

    public function testHandlerWithNoChange(): void
    {
        $edits = $this->format('<?php ');
        self::assertNull($edits, 'No-op should return NULL');
    }

    /**
     * @return TextEdit[]|null
     */
    private function format(string $document)
    {
        $formatter = new PhpCsFixerFormatter(__DIR__ . '/../../../../../vendor/bin/php-cs-fixer');
        $edits = wait($formatter->format(ProtocolFactory::textDocumentItem('file:///foo.php', $document)));
        return $edits;
    }
}
