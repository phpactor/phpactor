<?php
namespace Phpactor\Tests\Integration;

use Phpactor\Container\CoreExtension;

class CoreExtensionTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('Animals');
    }

    public function testDefaultConfig()
    {
        $core = new CoreExtension();
        $dir  = sprintf('%s/lib/Badger', $this->workspaceDir());
        chdir($dir);

        $conf = $core->getDefaultConfig();
        $this->assertSame($this->workspaceDir(), $conf['cwd']);
        $this->assertSame(sprintf('%s/vendor/autoload.php', $this->workspaceDir()), $conf['autoload']);
    }
}
