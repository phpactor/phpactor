<?php

namespace Phpactor\Extension\LanguageServerDiagnostics\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerDiagnostics\Model\PhpLinter;
use Phpactor\TextDocument\TextDocumentBuilder;
use function Amp\Promise\wait;

class PhpLinterTest extends TestCase
{
    public function testLintValid(): void
    {
        $document = TextDocumentBuilder::create('<?php class foobar {}')->build();
        $linter = new PhpLinter(PHP_BINARY);
        $diagnostics = wait($linter->lint($document));
        self::assertCount(0, $diagnostics);
    }

    public function testLintInvalid(): void
    {
        $document = TextDocumentBuilder::create('<?php cl foobar')->build();
        $linter = new PhpLinter(PHP_BINARY);
        $diagnostics = wait($linter->lint($document));
        self::assertCount(1, $diagnostics);
    }
}
