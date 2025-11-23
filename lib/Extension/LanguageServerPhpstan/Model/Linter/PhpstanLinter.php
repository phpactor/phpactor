<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model\Linter;

use Phpactor\VersionResolver\SemVersion;
use Phpactor\VersionResolver\SemVersionResolver;
use function Amp\call;
use Amp\Promise;
use Generator;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanProcess;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\TextDocument\TextDocumentUri;

class PhpstanLinter implements Linter
{
    public function __construct(
        private PhpstanProcess $phpstanProcess,
        private SemVersionResolver $versionResolver,
        private bool $disableTmpFile = false,
    ) {
    }

    public function isTmpFileDisabled(): bool
    {
        return $this->disableTmpFile;
    }

    public function lint(string $url, ?string $text): Promise
    {
        return call(function () use ($url, $text) {
            $version = yield $this->versionResolver->resolve();

            if (!$version instanceof SemVersion) {
                throw new \RuntimeException(sprintf(
                    'Could not determine PHPStan version'
                ));
            }

            $diagnostics = yield from $this->doLint($url, $text, $version);

            return $diagnostics;
        });
    }

    /**
     * @return Generator<Promise<array<Diagnostic>>>
     */
    private function doLint(string $url, ?string $text, SemVersion $version): Generator
    {
        $path = TextDocumentUri::fromString($url)->path();

        if (null === $text || $this->disableTmpFile) {
            return yield $this->phpstanProcess->analyseInPlace($path);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'phpstanls');
        file_put_contents($tempFile, $text);

        try {
            if ($version->greaterThanOrEqualTo(
                SemVersion::fromString('2.1.17'),
                SemVersion::fromString('1.12.27'),
            )) {
                return yield $this->phpstanProcess->editorModeAnalyse($path, $tempFile);
            }

            return yield $this->phpstanProcess->analyseInPlace($tempFile);
        } finally {
            @unlink($tempFile);
        }
    }
}
