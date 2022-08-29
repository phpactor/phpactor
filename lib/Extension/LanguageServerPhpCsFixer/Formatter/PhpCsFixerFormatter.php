<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Formatter;

use Amp\Process\Process;
use Amp\Promise;
use Phpactor\Extension\Rpc\Diff\TextEditBuilder;
use Phpactor\LanguageServerProtocol\TextEdit;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TextDocument\TextDocument;
use RuntimeException;
use function Amp\call;
 
class PhpCsFixerFormatter implements Formatter
{
    public function format(TextDocument $document): Promise
    {
        return call(function () use ($document) {
            return $this->toTextEdits($document->__toString(), yield $this->fixDocument($document));
        });
    }

    /**
     * @return Promise<string>
     */
    private function fixDocument(TextDocument $document): Promise
    {
        return call(function () use ($document) {
            $tempName = tempnam(sys_get_temp_dir(), 'phpactor_php_cs_fixer');
            if (false === $tempName) {
                throw new RuntimeException(
                    'Could get create temp name'
                );
            }
            if (false === file_put_contents($tempName, $document->__toString())) {
                throw new RuntimeException(
                    'Could not write temporary document'
                );
            }

            $process = new Process([
                'vendor/bin/php-cs-fixer',
                'fix',
                $tempName
            ]);
            $pid = yield $process->start();
            $exitCode = yield $process->join();
            if ($exitCode !== 0) {
                throw new RuntimeException(sprintf(
                    'php-cs-fixer exited with code "%s"',
                    $exitCode
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
        
        $builder = new TextEditBuilder();
        $edits = $builder->calculateTextEdits($document, $formatted);
        $lspEdits = [];
        foreach ($edits as $edit) {
            $lspEdits[] = new TextEdit(
                ProtocolFactory::range($edit['start']['line'], $edit['start']['character'], $edit['end']['line'], $edit['end']['character']),
                $edit['text']
            );
        }
        
        return $lspEdits;
    }
}
