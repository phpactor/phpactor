<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Adapter;

use Amp\Process\Process;
use Amp\Promise;
use Phpactor\Extension\LanguageServerReferenceFinder\Model\Highlighter;
use Phpactor\Extension\LanguageServerReferenceFinder\Model\Highlights;
use Phpactor\LanguageServerProtocol\DocumentHighlight;
use Phpactor\TextDocument\ByteOffset;
use RuntimeException;
use function Amp\ByteStream\buffer;
use function Amp\call;

class OutsourcedHighlighter implements Highlighter
{
    /**
     * @param list<string> $command
     */
    public function __construct(private array $command, private string $cwd)
    {
    }

    /**
     * @return Promise<Highlights>
     */
    public function highlightsFor(string $source, ByteOffset $offset): Promise
    {
        return call(function () use ($source, $offset) {
            $process = new Process(array_merge($this->command, [
                (string)$offset->toInt(),
            ]), $this->cwd);
            $pid = yield $process->start();

            $stdin = $process->getStdin();

            yield $stdin->write($source);
            $stdin->close();
            $json = yield buffer($process->getStdout());

            $exitCode = yield $process->join();
            if ($exitCode !== 0) {
                throw new RuntimeException(sprintf(
                    'Phpactor highlights process exited with code "%s": %s',
                    $exitCode,
                    yield buffer($process->getStderr())
                ));
            }
            $array = json_decode($json, true);
            if (!is_array($array)) {
                throw new RuntimeException(sprintf(
                    'Could not decode JSON: %s',
                    $json
                ));
            }

            return new Highlights(...array_map(fn (array $highlight) => DocumentHighlight::fromArray($highlight), $array));
        });
    }
}
