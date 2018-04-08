<?php

namespace Phpactor\Extension\Core\Rpc;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Core\Application\Status;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Config\Paths;

class StatusHandler implements Handler
{
    const STATUS = 'status';

    /**
     * @var Status
     */
    private $status;

    /**
     * @var Paths
     */
    private $paths;

    public function __construct(Status $status, Paths $paths)
    {
        $this->status = $status;
        $this->paths = $paths;
    }

    public function name(): string
    {
        return self::STATUS;
    }

    public function defaultParameters(): array
    {
        return [];
    }

    public function handle(array $arguments)
    {
        return EchoResponse::fromMessage(implode(
            PHP_EOL,
            [
                'Support',
                '-------',
                $this->buildSupportMessage(),
                'Config files',
                '------------',
                $this->buildConfigFileMessage(),
            ]
        ));
    }

    private function buildSupportMessage()
    {
        $diagnostics = $this->status->check();
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
        return implode(PHP_EOL, array_map(function ($file) {
            if (file_exists($file)) {
                return '[✔] ' . $file;
            }
            return '[✘] ' . $file;
        }, $this->paths->configFiles()));
    }
}
