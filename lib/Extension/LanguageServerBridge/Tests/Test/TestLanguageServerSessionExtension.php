<?php

namespace Phpactor\Extension\LanguageServerBridge\Tests\Test;

use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServer\LanguageServerSessionExtension;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\MapResolver\Resolver;

class TestLanguageServerSessionExtension implements Extension
{
    /**
     * @var LanguageServerSessionExtension
     */
    private $sessionExtension;

    public function __construct()
    {
        $transmitter = new TestMessageTransmitter();
        $this->sessionExtension = new LanguageServerSessionExtension(
            $transmitter,
            ProtocolFactory::initializeParams()
        );
    }
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $this->sessionExtension->load($container);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
        $this->sessionExtension->configure($schema);
    }
}
