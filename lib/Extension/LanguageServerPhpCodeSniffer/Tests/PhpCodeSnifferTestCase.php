<?php

namespace Phpactor\Extension\LanguageServerPhpCodeSniffer\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPhpCodeSniffer\Model\PhpCodeSnifferProcess;
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
