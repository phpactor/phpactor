<?php

namespace Phpactor\Extension\LanguageServerPhpCodeSniffer\Formatter;

use Amp\Promise;
use Phpactor\Extension\LanguageServerPhpCodeSniffer\Model\PhpCodeSnifferProcess;
use Phpactor\Diff\DiffToTextEditsConverter;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Formatting\Formatter;
use function Amp\call;

class PhpCodeSnifferFormatter implements Formatter
{
    public function __construct(
        private PhpCodeSnifferProcess $phpCodeSniffer
    ) {
    }

    public function format(TextDocumentItem $textDocument): Promise
    {
        return call(function () use ($textDocument) {
            $diff = yield $this->phpCodeSniffer->produceFixesDiff($textDocument);

            $diffToTextEdits = new DiffToTextEditsConverter();
            return $diffToTextEdits->toTextEdits($diff);
        });
    }
}
