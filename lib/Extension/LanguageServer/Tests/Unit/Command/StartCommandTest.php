<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\Command;

use Phpactor\Extension\LanguageServer\Tests\Unit\LanguageServerTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class StartCommandTest extends LanguageServerTestCase
{
    private CommandTester $tester;

    protected function setUp(): void
    {
        $container = $this->createContainer([]);
        $this->tester = new CommandTester($container->get('language_server.command.lsp_start'));
    }

    public function testCommandStarts(): void
    {
        $exitCode = $this->tester->execute([
            '--no-loop' => true,
            '--address' => '127.0.0.1:0',
        ]);
        self::assertEquals(0, $exitCode);
    }
}
