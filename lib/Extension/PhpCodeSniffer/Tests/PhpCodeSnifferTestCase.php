<?php

namespace Phpactor\Extension\PhpCodeSniffer\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\PhpCodeSniffer\Model\PhpCodeSnifferProcess;
use Psr\Log\NullLogger;

class PhpCodeSnifferTestCase extends TestCase
{
    public function getPhpCodeSniffer(): PhpCodeSnifferProcess
    {
        return new PhpCodeSnifferProcess(
            __DIR__ . '/../../../../vendor/bin/phpcs',
            new NullLogger(),
            [
                'XDEBUG_MODE' => 'off'
            ],
        );
    }
}
