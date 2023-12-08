<?php

namespace Phpactor\Amp\Process;

use Amp\Process\Process;
use Amp\Process\StatusError;
use Psr\Log\LoggerInterface;
use function Amp\asyncCall;
use function Amp\delay;

class ProcessUtil
{
    public static function killAfter(LoggerInterface $logger, Process $process, int $timeout): void
    {
        $start = time();
        asyncCall(function () use ($logger, $process, $start, $timeout) {
            while ($process->isRunning()) {
                yield delay(500);
                // phpstan doesn't expect that $process->isRunning() output can change
                // @phpstan-ignore-next-line
                if (time() >= $start + $timeout && $process->isRunning()) {
                    try {
                        $process->kill();
                        $logger->warning(sprintf(
                            'Killed process "%s" (%s) because it lived longer than %ds',
                            $process->getPid(),
                            $process->getCommand(),
                            $timeout
                        ));
                    } catch (StatusError $e) {
                    }
                    break;
                }
            }
        });
    }
}
