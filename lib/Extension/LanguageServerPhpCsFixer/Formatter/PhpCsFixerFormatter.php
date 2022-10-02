<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Formatter;

use Amp\Process\Process;
use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\TextEdit;
use Phpactor\LanguageServer\Core\Formatting\Formatter;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TextDocument\ByteOffset;
use RuntimeException;
use function Amp\ByteStream\buffer;
use function Amp\call;

class PhpCsFixerFormatter implements Formatter
{
    private string $binPath;

    public function __construct(string $binPath)
    {
        $this->binPath = $binPath;
    }

    public function format(TextDocumentItem $document): Promise
    {
        return call(function () use ($document) {
            return $this->toTextEdits($document->text, yield $this->fixDocument($document));
        });
    }

    /**
     * @return Promise<string>
     */
    private function fixDocument(TextDocumentItem $document): Promise
    {
        return call(function () use ($document) {
            $tempName = tempnam(sys_get_temp_dir(), 'phpactor_php_cs_fixer');
            if (false === $tempName) {
                throw new RuntimeException(
                    'Could get create temp name'
                );
            }
            if (false === file_put_contents($tempName, $document->text)) {
                throw new RuntimeException(
                    'Could not write temporary document'
                );
            }

            $process = new Process([
                $this->binPath,
                'fix',
                $tempName
            ]);
            $pid = yield $process->start();
            $exitCode = yield $process->join();
            if ($exitCode !== 0) {
                throw new RuntimeException(sprintf(
                    'php-cs-fixer exited with code "%s": %s',
                    $exitCode,
                    yield buffer($process->getStderr())
                ));
            }

            $formatted = file_get_contents($tempName);
            unlink($tempName);

            return $formatted;
        });
    }

    /**
     * @return null|array<int,TextEdit>
     */
    private function toTextEdits(string $document, string $formatted): ?array
    {
        if ($document === $formatted) {
            return null;
        }

        $lineCol = PositionConverter::byteOffsetToPosition(
            ByteOffset::fromInt(strlen($document)),
            $document
        );

        $lspEdits = [
            new TextEdit(
                ProtocolFactory::range(
                    0,
                    0,
                    $lineCol->line,
                    $lineCol->character
                ),
                $formatted
            )
        ];

        return $lspEdits;
    }
}
