<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Unit\Service;

use Phpactor\Extension\ExtensionManager\Model\Exception\CouldNotInstallExtension;
use Phpactor\Extension\ExtensionManager\Model\Extension;
use Phpactor\Extension\ExtensionManager\Model\ExtensionConfig;
use Phpactor\Extension\ExtensionManager\Model\ExtensionConfigLoader;
use Phpactor\Extension\ExtensionManager\Model\ExtensionRepository;
use Phpactor\Extension\ExtensionManager\Model\Installer;
use Phpactor\Extension\ExtensionManager\Model\VersionFinder;
use Phpactor\Extension\ExtensionManager\Service\InstallerService;
use Phpactor\Extension\ExtensionManager\Service\ProgressLogger;
use Phpactor\Extension\ExtensionManager\Tests\TestCase;

class InstallerServiceTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $installer;

    /**
     * @var ObjectProphecy
     */
    private $config;

    /**
     * @var ObjectProphecy
     */
    private $finder;

    /**
     * @var ObjectProphecy
     */
    private $extension1;

    /**
     * @var InstallerService
     */
    private $service;

    /**
     * @var ObjectProphecy
     */
    private $repository;

    /**
     * @var ObjectProphecy
     */
    private $progress;

    /**
     * @var ObjectProphecy
     */
    private $factory;

    public function setUp(): void
    {
        $this->installer = $this->prophesize(Installer::class);
        $this->factory = $this->prophesize(ExtensionConfigLoader::class);
        $this->config = $this->prophesize(ExtensionConfig::class);
        $this->finder = $this->prophesize(VersionFinder::class);
        $this->repository = $this->prophesize(ExtensionRepository::class);
        $this->progress = $this->prophesize(ProgressLogger::class);

        $this->service = new InstallerService(
            $this->installer->reveal(),
            $this->factory->reveal(),
            $this->finder->reveal(),
            $this->repository->reveal(),
            $this->progress->reveal()
        );

        $this->factory->load()->willReturn($this->config->reveal());
        $this->extension1 = $this->prophesize(Extension::class);
    }

    public function testRequireExtensions(): void
    {
        $this->finder->findBestVersion('foobar')->willReturn('dev-foo');
        $this->config->require('foobar', 'dev-foo')->shouldBeCalled();
        $this->config->write()->shouldBeCalled();
        $this->installer->installForceUpdate()->shouldBeCalled();
        $this->progress->log('Using version "dev-foo"')->shouldBeCalled();

        $this->service->requireExtensions(['foobar']);
    }

    public function testRollsbackConfigOnError(): void
    {
        $this->finder->findBestVersion('foobar')->willReturn('dev-foo');
        $this->config->require('foobar', 'dev-foo')->shouldBeCalled();
        $this->config->write()->shouldBeCalled();
        $this->installer->installForceUpdate()->willThrow(new CouldNotInstallExtension('foo'));
        $this->config->revert()->shouldBeCalled();

        try {
            $this->service->requireExtensions(['foobar']);
        } catch (CouldNotInstallExtension $e) {
        }
    }
}
