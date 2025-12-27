<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Adapter\VersionResolver;

use Amp\Promise;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanProcess;
use Phpactor\VersionResolver\SemVersion;
use Phpactor\VersionResolver\SemVersionResolver;
use function Amp\call;

class PhpstanVersionResolver implements SemVersionResolver
{
    public function __construct(private readonly PhpstanProcess $process)
    {
    }

    public function resolve(): Promise
    {
        return call(function () {
            $versionString = yield $this->process->version();
            if (!is_string($versionString)) {
                return null;
            }
            return SemVersion::fromString($versionString);
        });
    }
}
