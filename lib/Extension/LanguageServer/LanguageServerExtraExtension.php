<?php

namespace Phpactor\Extension\LanguageServer;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Core\CoreExtension;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\LanguageServer\Service\OnDevelopWarningService;

class LanguageServerExtraExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(OnDevelopWarningService::class, function (Container $container) {
            return new OnDevelopWarningService(
                $container->get(ClientApi::class),
                $container->get('application.status'),
                $container->parameter(CoreExtension::PARAM_WARN_ON_DEVELOP)->bool()
            );
        }, [
            LanguageServerExtension::TAG_SERVICE_PROVIDER => []
        ]);
    }


    public function configure(Resolver $schema): void
    {
    }
}
