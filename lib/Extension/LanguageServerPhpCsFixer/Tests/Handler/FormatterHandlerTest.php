<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Tests\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerBridge\TextDocument\WorkspaceTextDocumentLocator;
use Phpactor\Extension\LanguageServerPhpCsFixer\Formatter\PhpCsFixerFormatter;
use Phpactor\Extension\LanguageServerPhpCsFixer\Handler\FormattingHandler;
use Phpactor\LanguageServerProtocol\DocumentFormattingParams;
use Phpactor\LanguageServerProtocol\FormattingOptions;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\ProtocolFactory;

class FormatterHandlerTest extends TestCase
{
    public function testHandler(): void
    {
        $builder = LanguageServerTesterBuilder::createBare()
            ->enableTextDocuments();

        $builder
            ->addHandler(new FormattingHandler(
                new PhpCsFixerFormatter(),
                new WorkspaceTextDocumentLocator($builder->workspace())
            ));
        $server = $builder->build();

        $server->textDocument()->open('file:///foobar', '<?php exit();');
        $server->requestAndWait(
            'textDocument/formatting',
            new DocumentFormattingParams(
                ProtocolFactory::textDocumentIdentifier('file:///foobar'),
                new FormattingOptions(4, false)
            )
        );
    }



}
