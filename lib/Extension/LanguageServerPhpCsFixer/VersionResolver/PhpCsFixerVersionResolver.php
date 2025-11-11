<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\VersionResolver;

use Phpactor\Extension\LanguageServerPhpCsFixer\Model\PhpCsFixerProcess;
use Phpactor\VersionResolver\SemVersion;
use Phpactor\VersionResolver\SemVersionResolver;

use Psr\Log\LoggerInterface;
use function Amp\ByteStream\buffer;
use function Amp\Promise\wait;

class PhpCsFixerVersionResolver implements SemVersionResolver
{
    public function __construct(
        private ?string $version = null,
        private string $binPath,
        private LoggerInterface $logger,
    ) {
    }

    public function resolve(): ?SemVersion
    {
        if (null !== $this->version) {
            return new SemVersion($this->version);
        }

        $versionQuery = wait((new PhpCsFixerProcess($this->binPath, $this->logger))->run('--version'));
        $stdout = wait(buffer($versionQuery->getStdout()));

        if (wait($versionQuery->join()) !== 0) {
            return null;
        }

        preg_match('/^PHP CS Fixer (\d+\.\d+\.\d+) /', $stdout, $version);

        return new SemVersion($version[1]) ?? null;
    }
}
