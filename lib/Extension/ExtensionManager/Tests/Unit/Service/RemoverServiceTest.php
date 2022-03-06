<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Unit\Service;

use Exception;
use Phpactor\Extension\ExtensionManager\Model\DependentExtensionFinder;
use Phpactor\Extension\ExtensionManager\Model\Extension;
use Phpactor\Extension\ExtensionManager\Model\ExtensionConfig;
use Phpactor\Extension\ExtensionManager\Model\ExtensionConfigLoader;
use Phpactor\Extension\ExtensionManager\Model\ExtensionRepository;
use Phpactor\Extension\ExtensionManager\Model\ExtensionState;
use Phpactor\Extension\ExtensionManager\Model\Installer;
use Phpactor\Extension\ExtensionManager\Service\RemoverService;
use Phpactor\Extension\ExtensionManager\Tests\TestCase;
use RuntimeException;

class RemoverServiceTest extends TestCase
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
    private $repository;
    /**
     * @var RemoverService
     */
    private $service;
    /**
     * @var ObjectProphecy
     */
    private $extension1;

    /**
     * @var ObjectProphecy
     */
    private $configLoader;

    public function setUp(): void
    {
        $this->installer = $this->prophesize(Installer::class);
        $this->configLoader = $this->prophesize(ExtensionConfigLoader::class);
        $this->config = $this->prophesize(ExtensionConfig::class);
        $this->finder = $this->prophesize(DependentExtensionFinder::class);
        $this->repository = $this->prophesize(ExtensionRepository::class);

        $this->service = new RemoverService(
            $this->installer->reveal(),
            $this->finder->reveal(),
            $this->repository->reveal(),
            $this->configLoader->reveal()
        );

        $this->configLoader->load()->willReturn($this->config->reveal());
        $this->extension1 = $this->prophesize(Extension::class);
    }

    public function testRemoveExtension(): void
    {
        $this->repository->find('foobar')->willReturn($this->extension1->reveal());
        $this->extension1->state()->willReturn(new ExtensionState(ExtensionState::STATE_SECONDARY));

        $this->config->unrequire('foobar')->shouldBeCalled();
        $this->config->write()->shouldBeCalled();
        $this->installer->installForceUpdate()->shouldBeCalled();

        $this->service->removeExtension('foobar');
    }

    public function testRevertsConfigOnError(): void
    {
        $this->repository->find('foobar')->willReturn($this->extension1->reveal());
        $this->extension1->state()->willReturn(new ExtensionState(ExtensionState::STATE_SECONDARY));

        $this->config->unrequire('foobar')->shouldBeCalled();
        $this->config->write()->shouldBeCalled();
        $this->installer->installForceUpdate()->willThrow(new Exception('foo'));
        $this->config->revert()->shouldBeCalled();

        try {
            $this->service->removeExtension('foobar');
        } catch (Exception $e) {
        }
    }

    public function testThrowsExceptionIfExtensionIsPrimary(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is a primary');

        $this->repository->find('foobar')->willReturn($this->extension1->reveal());
        $this->extension1->state()->willReturn(new ExtensionState(ExtensionState::STATE_PRIMARY));

        $this->service->removeExtension('foobar');
    }
}
