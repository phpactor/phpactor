<?php

namespace Phpactor\Tests\Integration\Extension\Core;

use Phpactor\Tests\IntegrationTestCase;
use Phpactor\Extension\Core\Application\CacheClear;

class CacheClearTest extends IntegrationTestCase
{
    private CacheClear $cacheClear;

    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->workspace()->loadManifest(
            <<<'EOT'
                // File: test.text
                Hello World
                // File: folder/test.text
                Hello World
                EOT
        );
        $this->cacheClear = new CacheClear($this->workspaceDir());
    }

    public function testCacheClear(): void
    {
        $this->assertTrue($this->workspace()->exists('/test.text'));
        $this->assertTrue($this->workspace()->exists('/folder/test.text'));

        $this->cacheClear->clearCache();

        $this->assertFalse($this->workspace()->exists('/test.text'));
        $this->assertFalse($this->workspace()->exists('/folder/test.text'));
    }
}
