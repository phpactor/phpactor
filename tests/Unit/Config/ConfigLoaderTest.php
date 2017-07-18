<?php

namespace Phpactor\Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use XdgBaseDir\Xdg;
use Phpactor\Config\ConfigLoader;

class ConfigLoaderTest extends TestCase
{
    public function testLoaderConfig()
    {
        $xdg = $this->prophesize(Xdg::class);
        $xdg->getConfigDirs()->willReturn([
            __DIR__ .'/config/xdg',
            __DIR__ .'/config/user',
        ]);

        $configLoader = new ConfigLoader($xdg->reveal());
        $config = $configLoader->loadConfig();
    }
}
