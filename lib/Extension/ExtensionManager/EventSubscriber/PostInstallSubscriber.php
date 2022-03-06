<?php

namespace Phpactor\Extension\ExtensionManager\EventSubscriber;

use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Phpactor\Extension\ExtensionManager\Adapter\Composer\PackageExtensionFactory;
use Phpactor\Extension\ExtensionManager\Model\ExtensionFileGenerator;

class PostInstallSubscriber implements EventSubscriberInterface
{
    /**
     * @var ExtensionFileGenerator
     */
    private $extensionWriter;

    /**
     * @var PackageExtensionFactory
     */
    private $factory;

    public function __construct(ExtensionFileGenerator $extensionWriter, PackageExtensionFactory $factory)
    {
        $this->extensionWriter = $extensionWriter;
        $this->factory = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'handlePostPackageInstall',
            ScriptEvents::POST_UPDATE_CMD => 'handlePostPackageInstall',
        ];
    }

    public function handlePostPackageInstall(Event $event): void
    {
        $repository = $event->getComposer()->getRepositoryManager()->getLocalRepository();

        $this->extensionWriter->writeExtensionList(
            $this->factory->fromPackages($repository->getPackages())
        );
    }
}
