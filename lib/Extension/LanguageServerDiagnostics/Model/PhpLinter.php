<?php

namespace Phpactor\Extension\LanguageServerDiagnostics\Model;

use Amp\Process\Process;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\Util\LineColRangeForLine;
use function Amp\ByteStream\buffer;
use function Amp\call;

final class PhpLinter
{
    public function __construct(private readonly string $phpBin)
    {
    }

    /**
     * @return Promise<Diagnostic[]>
     */
    public function lint(TextDocument $textDocument): Promise
    {
        return call(function () use ($textDocument) {
            $process = new Process([
                $this->phpBin,
                '-l',
                '-d',
                'display_errors=stdout',
            ]);
            $pid = yield $process->start();
            yield $process->getStdin()->write($textDocument->__toString());
            yield $process->getStdin()->end();
            $exitCode = yield $process->join();

            if ($exitCode == 0) {
                return [];
            }

            $err = yield buffer($process->getStdout());

            if (!$err) {
                return [];
            }

            if (!preg_match('/line ([0-9]+)/i', $err, $line)) {
                return [];
            }

            $line = (int)$line[1] - 1;
            $range = (new LineColRangeForLine())->rangeFromLine($textDocument->__toString(), $line + 1);

            return [
                new Diagnostic(
                    range: new Range(
                        new Position($line, $range->start()->col() - 1),
                        new Position($line, $range->end()->col() - 1)
                    ),
                    message: $err,
                    severity: DiagnosticSeverity::ERROR
                )
            ];
        });
    }
}
