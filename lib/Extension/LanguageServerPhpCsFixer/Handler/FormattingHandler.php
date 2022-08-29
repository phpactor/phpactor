<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Handler;

use Amp\Promise;
use Phpactor\Extension\LanguageServerPhpCsFixer\Formatter\PhpCsFixerFormatter;
use Phpactor\Extension\Rpc\Diff\TextEditBuilder;
use Phpactor\LanguageServerProtocol\FormattingOptions;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServerProtocol\TextEdit;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use function Amp\call;

class FormattingHandler implements Handler, CanRegisterCapabilities
{
    private PhpCsFixerFormatter $formatter;
    private TextDocumentLocator $locator;

    public function __construct(PhpCsFixerFormatter $formatter, TextDocumentLocator $locator)
    {
        $this->formatter = $formatter;
        $this->locator = $locator;
    }

    public function methods(): array
    {
        return ['textDocument/formatting' => 'formatting'];
    }

    /**
     * @return Promise<array<int,TextEdit[]>|null>
     */
    public function formatting(TextDocumentIdentifier $textDocument, FormattingOptions $options): Promise
    {
        return call(function () use ($textDocument) {
            $builder = new TextEditBuilder();
            $document = $this->locator->get(TextDocumentUri::fromString($textDocument->uri));
            $formatted = $this->formatter->format($document);

            if ($document->__toString() === $formatted->__toString()) {
                return null;
            }

            $edits = $builder->calculateTextEdits($document->__toString(), $formatted);
            $lspEdits = [];
            foreach ($edits as $edit) {
                $lspEdits[] = new TextEdit(
                    ProtocolFactory::range($edit['start']['line'], $edit['start']['character'], $edit['end']['line'], $edit['end']['character']),
                    $edit['text']
                );
            }

            return $lspEdits;
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->documentFormattingProvider = true;
    }
}
