<?php

namespace Phpactor\Extension\Core\Rpc;

use Phpactor\ConfigLoader\Core\PathCandidate;
use Phpactor\ConfigLoader\Core\PathCandidates;
use Phpactor\Extension\Rpc\Response\ReturnResponse;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Core\Application\Status;
use Phpactor\Extension\Rpc\Response\EchoResponse;

class StatusHandler implements Handler
{
    const NAME = 'status';
    const PARAM_TYPE = 'type';
    const TYPE_FORMATTED = 'formatted';
    const TYPE_DETAILED = 'detailed';

    private Status $status;

    private PathCandidates $paths;

    public function __construct(Status $status, PathCandidates $paths)
    {
        $this->status = $status;
        $this->paths = $paths;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_TYPE => self::TYPE_FORMATTED,
        ]);
    }

    public function handle(array $arguments)
    {
        $diagnostics = $this->status->check();

        switch ($arguments[self::PARAM_TYPE]) {
            case self::TYPE_FORMATTED:
                $response = $this->handleFormattedType($diagnostics);
                break;

            case self::TYPE_DETAILED:
            default:
                $response = $this->handleDetailedType($diagnostics);
                break;
        }

        return $response;
    }

    private function handleDetailedType(array $status): ReturnResponse
    {
        $status['diagnostics'] = \array_merge(
            \array_fill_keys($status['good'], true),
            \array_fill_keys($status['bad'], false)
        );

        unset($status['good']);
        unset($status['bad']);

        return ReturnResponse::fromValue($status);
    }

    private function handleFormattedType(array $diagnostics): EchoResponse
    {
        $info = [
            'Info',
            '----',
            'Version: ' . $diagnostics['phpactor_version'],
            'PHP: ' . sprintf('%s (supporting %s)', phpversion(), $diagnostics['php_version']),
            'Phpactor dir: ' . realpath(__DIR__ . '/../../../../'),
            'Work dir: ' . $diagnostics['cwd'] . PHP_EOL,
            'Diagnostics',
            '-----------',
            $this->buildSupportMessage($diagnostics),
            'Config files',
            '------------',
            $this->buildConfigFileMessage(),
        ];
        if ($diagnostics['phpactor_is_develop']) {
            $info[] = '';
            $info[] = 'WARNING';
            $info[] = '-------';
            $info[] = 'You are using the develop branch which is no longer maintained';
            $info[] = 'Switch to master or use the latest tagged version of Phpactor';
        }
        return EchoResponse::fromMessage(implode(PHP_EOL, $info));
    }

    private function buildSupportMessage(array $diagnostics)
    {
        return implode(PHP_EOL, [
            implode(PHP_EOL, array_map(function (string $message) {
                return '[✔] ' . $message;
            }, $diagnostics['good'])),
            implode(PHP_EOL, array_map(function (string $message) {
                return '[✘] ' . $message;
            }, $diagnostics['bad'])),
        ]);
    }

    private function buildConfigFileMessage()
    {
        return implode(PHP_EOL, array_map(function (PathCandidate $file) {
            if (file_exists($file->path())) {
                return '[✔] ' . $file->path();
            }
            return '[✘] ' . $file->path();
        }, iterator_to_array($this->paths)));
    }
}
