<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Tests\Formatter;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPhpCsFixer\Formatter\PhpCsFixerFormatter;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use function Amp\Promise\wait;

class PhpCsFormatterTest extends TestCase
{
    public function testHandler(): void
    {
        $formatter = new PhpCsFixerFormatter(__DIR__ . '/../../../../../vendor/bin/php-cs-fixer');
        $edits = wait($formatter->format(ProtocolFactory::textDocumentItem('file:///foo.php', '<?php echo "hello";')));
        self::assertCount(1, $edits);

        // arbitrary transformation replaced " with '
        self::assertEquals('<?php echo \'hello\';', trim($edits[0]->newText));

    }
}
