<?php

namespace Phpactor\Extension\PhpCodeSniffer\Tests\Model;

use Phpactor\Extension\PhpCodeSniffer\Tests\PhpCodeSnifferTestCase;
use function Amp\ByteStream\buffer;
use function Amp\Promise\wait;
use function Amp\call;

class PhpCodeSnifferProcessTest extends PhpCodeSnifferTestCase
{
    public function testRun(): void
    {
        $phpCodeSniffer = $this->getPhpCodeSniffer();

        $process = call(function () use ($phpCodeSniffer) {
            $process = yield $phpCodeSniffer->run('--version');
            $stdout = yield buffer($process->getStdout());

            self::assertStringContainsString('PHP_CodeSniffer ', $stdout, sprintf("Expected phpcs --version to return it's name followed with version, got: %s", $stdout));
        });

        wait($process);
    }

}
