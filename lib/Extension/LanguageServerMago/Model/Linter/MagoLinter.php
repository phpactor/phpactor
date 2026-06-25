<?php

namespace Phpactor\Extension\LanguageServerMago\Model\Linter;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\Extension\LanguageServerMago\Model\Linter;
use Phpactor\Extension\LanguageServerMago\Model\MagoProcess;
use Phpactor\TextDocument\TextDocumentUri;
use Symfony\Component\Filesystem\Path;
use function Amp\call;

/**
 * Lints a single document with one Mago subcommand (analyze or lint), reporting
 * diagnostics under one source name.
 */
class MagoLinter implements Linter
{
    public function __construct(
        private MagoProcess $process,
        private string $projectRoot,
        private string $subcommand,
        private string $source,
    ) {
    }

    public function lint(string $url, string $text, CancellationToken $cancel): Promise
    {
        return call(function () use ($url, $text, $cancel) {
            // Only on-disk documents within the project can be expressed as a
            // workspace-relative path for Mago. Untitled buffers and files
            // outside the project root are skipped.
            if (!str_starts_with($url, 'file:')) {
                return [];
            }

            $path = TextDocumentUri::fromString($url)->path();

            // Skip anything not contained in the project root (the root itself
            // yields an empty relative path).
            if (!Path::isBasePath($this->projectRoot, $path)) {
                return [];
            }

            $relativePath = Path::makeRelative($path, $this->projectRoot);

            if ($relativePath === '') {
                return [];
            }

            return yield $this->process->analyse(
                $this->subcommand,
                $this->source,
                $relativePath,
                $url,
                $text,
                $cancel,
            );
        });
    }
}
