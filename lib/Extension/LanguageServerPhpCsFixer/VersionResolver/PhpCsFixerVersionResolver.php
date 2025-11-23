<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\VersionResolver;

use Amp\Promise;
use Phpactor\Extension\LanguageServerPhpCsFixer\Model\PhpCsFixerProcess;
use Phpactor\VersionResolver\SemVersion;
use Phpactor\VersionResolver\SemVersionResolver;
use Psr\Log\LoggerInterface;

use function Amp\ByteStream\buffer;
use function Amp\call;

class PhpCsFixerVersionResolver implements SemVersionResolver
{
    public function __construct(
        private string $binPath,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return Promise<?SemVersion>
     */
    public function resolve(): Promise
    {
        return call(function () {
            $versionQuery = yield (new PhpCsFixerProcess($this->binPath, $this->logger))->run([], '--version');
            $stdout = yield buffer($versionQuery->getStdout());
            $exitCode = yield $versionQuery->join();

            if ($exitCode !== 0) {
                return null;
            }

            preg_match('/^PHP CS Fixer (\d+\.\d+\.\d+) /', $stdout, $version);

            return (count($version) > 0) ? SemVersion::fromString($version[1]) : null;
        });
    }
}
