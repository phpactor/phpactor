<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Formatter;

use Amp\Promise;
use Phpactor\Extension\LanguageServerPhpCsFixer\Model\PhpCsFixerProcess;
use Phpactor\Extension\LanguageServerPhpCsFixer\Util\DiffToTextEditsConverter;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Formatting\Formatter;
use function Amp\call;

class PhpCsFixerFormatter implements Formatter
{
    public function __construct(
        private PhpCsFixerProcess $phpCsFixer
    ) {
    }

    public function format(TextDocumentItem $textDocument): Promise
    {
        return call(function () use ($textDocument) {
            $diff = yield $this->phpCsFixer->fix($textDocument->text, ['--diff', '--dry-run']);

            $diffToTextEdits = new DiffToTextEditsConverter();
            $textEdits = $diffToTextEdits->toTextEdits($diff);

            return $textEdits;
        });
    }
}
