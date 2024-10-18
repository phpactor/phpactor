<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model\Linter;

use function Amp\call;
use Amp\Promise;
use Generator;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanProcess;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\TextDocument\TextDocumentUri;
use function Safe\tempnam;
use function Safe\file_put_contents;

class PhpstanLinter implements Linter
{
    public function __construct(private PhpstanProcess $process)
    {
    }

    public function lint(string $url, ?string $text): Promise
    {
        return call(function () use ($url, $text) {
            $diagnostics = yield from $this->doLint($url, $text);

            return $diagnostics;
        });
    }

    /**
     * @return Generator<Promise<array<Diagnostic>>>
     */
    private function doLint(string $url, ?string $text): Generator
    {
        if (null === $text) {
            return yield $this->process->analyse(TextDocumentUri::fromString($url)->path());
        }

        $name = tempnam(sys_get_temp_dir(), 'phpstanls');
        file_put_contents($name, $text);
        $diagnostics = yield $this->process->analyse($name);
        unlink($name);
        return $diagnostics;
    }
}
