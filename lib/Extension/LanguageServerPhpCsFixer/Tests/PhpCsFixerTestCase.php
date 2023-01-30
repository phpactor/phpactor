<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPhpCsFixer\Model\PhpCsFixerProcess;
use Psr\Log\NullLogger;

class PhpCsFixerTestCase extends TestCase
{
    public function getPhpCsFixer(): PhpCsFixerProcess
    {
        return new PhpCsFixerProcess(
            __DIR__ . '/../../../../vendor/bin/php-cs-fixer',
            new NullLogger(),
            [
                'PHP_CS_FIXER_IGNORE_ENV' => '1',
                'XDEBUG_MODE' => 'off'
            ],
        );
    }
}
