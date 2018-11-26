<?php

namespace Phpactor\Tests\Unit\Config;

use Phpactor\Config\Paths;
use PHPUnit\Framework\TestCase;
use XdgBaseDir\Xdg;

class PathsTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $xdg;

    /**
     * @var Paths
     */
    private $paths;


    protected function setUp()
    {
        $this->xdg = $this->prophesize(Xdg::class);
        $this->xdg->getConfigDirs()->willReturn([
            'config/user',
            'config/xdg',
        ]);
        $this->xdg->getHomeDataDir()->willReturn('home');
        $this->paths = new Paths($this->xdg->reveal(), 'config');
    }

    public function testReturnsConfigPaths()
    {
        $paths = $this->paths->configPaths();

        $this->assertEquals('config/xdg/phpactor', $paths[0]);
        $this->assertEquals('config/user/phpactor', $paths[1]);
    }

    public function testReturnsConfigFiles()
    {
        $paths = $this->paths->configFiles();

        $this->assertEquals('config/xdg/phpactor/phpactor.yml', $paths[0]);
        $this->assertEquals('config/user/phpactor/phpactor.yml', $paths[1]);
        $this->assertEquals('config/.phpactor.yml', $paths[2]);
    }

    public function testReturnsConfigPathsWithSuffix()
    {
        $paths = $this->paths->configPaths('templates');

        $this->assertEquals('config/xdg/phpactor/templates', $paths[0]);
        $this->assertEquals('config/user/phpactor/templates', $paths[1]);
    }
}
