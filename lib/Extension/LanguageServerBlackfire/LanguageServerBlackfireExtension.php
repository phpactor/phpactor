<?php

namespace Phpactor\Extension\LanguageServerBlackfire;

use Blackfire\Client;
use Blackfire\ClientConfiguration;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerBlackfire\Handler\BlackfireHandler;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\MapResolver\Resolver;

class LanguageServerBlackfireExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(BlackfireHandler::class, function (Container $container) {
            return new BlackfireHandler(
                new Client(),
                $container->get(ClientApi::class)
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => []]);
    }

    public function configure(Resolver $schema): void
    {
    }
}
