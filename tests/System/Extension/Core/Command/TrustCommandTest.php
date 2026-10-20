<?php

namespace Phpactor\Tests\System\Extension\Core\Command;

use Phpactor\Tests\System\SystemTestCase;

class TrustCommandTest extends SystemTestCase
{
    public function testTrust(): void
    {
        $process = $this->phpactorFromStringArgs('config:trust --trust');
        $this->assertSuccess($process);
    }

    public function testTrustsWildcardPatternFromTrustFile(): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('phpactor/trust.json', (string) json_encode([
            $this->workspaceDir() . '/*' => true,
        ]));
        $this->workspace()->mkdir('project');
        $this->workspace()->put('project/.phpactor.json', '{"behat.enabled": true}');

        $process = $this->phpactorFromStringArgs(
            sprintf('config:dump --working-dir=%s', escapeshellarg($this->workspaceDir() . '/project')),
            null,
            ['PHPACTOR_UNCONDITIONAL_TRUST' => false]
        );

        $this->assertSuccess($process);
        self::assertStringContainsString('"core.trusted": true', $process->getOutput());
        self::assertStringContainsString('"behat.enabled": true', $process->getOutput());
    }
}
