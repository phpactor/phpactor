<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Tests;

use Amp\Promise;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPhpCsFixer\Model\PhpCsFixerProcess;
use Psr\Log\NullLogger;

class PhpCsFixerTestCase extends TestCase
{
    public function getPhpCsFixer(): PhpCsFixerProcess
    {
        return new class(__DIR__ . '/../../../../vendor/bin/php-cs-fixer', new NullLogger(), [ 'PHP_CS_FIXER_IGNORE_ENV' => '1', 'XDEBUG_MODE' => 'off' ], ) extends PhpCsFixerProcess {
            public function fix(string $content, array $options = []): Promise
            {
                return parent::fix($content, array_merge($options, ['--no-ansi']));
            }

            public function describe(string $rule, array $options = []): Promise
            {
                return parent::describe($rule, array_merge($options, ['--no-ansi']));
            }
        };
    }
}
