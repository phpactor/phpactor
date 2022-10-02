<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Handler;

use Amp\Promise;
use Phpactor\Extension\LanguageServerPhpCsFixer\Formatter\PhpCsFixerFormatter;
use Phpactor\LanguageServerProtocol\FormattingOptions;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServerProtocol\TextEdit;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
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
            $document = $this->locator->get(TextDocumentUri::fromString($textDocument->uri));
            $formatted = yield $this->formatter->format($document);

            return $formatted;
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->documentFormattingProvider = true;
    }
}
