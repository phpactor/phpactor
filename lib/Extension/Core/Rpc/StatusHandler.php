<?php

namespace Phpactor\Extension\Core\Rpc;

use Phpactor\ConfigLoader\Core\PathCandidate;
use Phpactor\ConfigLoader\Core\PathCandidates;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Core\Application\Status;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Config\Paths;

class StatusHandler implements Handler
{
    const NAME = 'status';

    /**
     * @var Status
     */
    private $status;

    /**
     * @var Paths
     */
    private $paths;

    public function __construct(Status $status, PathCandidates $paths)
    {
        $this->status = $status;
        $this->paths = $paths;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver)
    {
    }

    public function handle(array $arguments)
    {
        $diagnostics = $this->status->check();
        return EchoResponse::fromMessage(implode(
            PHP_EOL,
            [
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
            ]
        ));
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
