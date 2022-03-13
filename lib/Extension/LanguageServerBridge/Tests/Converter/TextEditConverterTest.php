<?php

namespace Phpactor\Extension\LanguageServerBridge\Tests\Converter;

use Phpactor\Extension\LanguageServerBridge\TextDocument\WorkspaceTextDocumentLocator;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\Extension\LanguageServerBridge\Tests\IntegrationTestCase;
use Phpactor\LanguageServerProtocol\TextEdit as LspTextEdit;

class TextEditConverterTest extends IntegrationTestCase
{
    private Workspace $workspace;

    private TextEditConverter $converter;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->workspace = new Workspace();
        $this->converter = new TextEditConverter(new LocationConverter(new WorkspaceTextDocumentLocator($this->workspace)));
    }

    public function testConvertsTextEdits(): void
    {
        $text = '1234567890';
        self::assertEquals([
            new LspTextEdit(new Range(
                new Position(0, 1),
                new Position(0, 4),
            ), 'foo'),
        ], $this->converter->toLspTextEdits(TextEdits::one(TextEdit::create(1, 3, 'foo')), $text));
    }
}
