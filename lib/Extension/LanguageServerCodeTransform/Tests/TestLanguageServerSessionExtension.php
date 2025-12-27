<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests;

use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServer\LanguageServerSessionExtension;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\MapResolver\Resolver;

class TestLanguageServerSessionExtension implements Extension
{
    private readonly LanguageServerSessionExtension $sessionExtension;

    public function __construct()
    {
        $transmitter = new TestMessageTransmitter();
        $this->sessionExtension = new LanguageServerSessionExtension(
            $transmitter,
            ProtocolFactory::initializeParams()
        );
    }


    public function load(ContainerBuilder $container): void
    {
        $this->sessionExtension->load($container);
    }


    public function configure(Resolver $schema): void
    {
        $this->sessionExtension->configure($schema);
    }
}
