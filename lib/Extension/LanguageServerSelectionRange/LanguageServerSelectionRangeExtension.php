<?php

namespace Phpactor\Extension\LanguageServerSelectionRange;

use Microsoft\PhpParser\Parser;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerSelectionRange\Handler\SelectionRangeHandler;
use Phpactor\Extension\LanguageServerSelectionRange\Model\RangeProvider;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\MapResolver\Resolver;

class LanguageServerSelectionRangeExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register(SelectionRangeHandler::class, function (Container $container) {
            return new SelectionRangeHandler(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(RangeProvider::class)
            );
        }, [
            LanguageServerExtension::TAG_METHOD_HANDLER => [],
        ]);
        $container->register(RangeProvider::class, function (Container $container) {
            return new RangeProvider(new Parser());
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
    }
}
