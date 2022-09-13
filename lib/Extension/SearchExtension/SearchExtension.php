<?php

namespace Phpactor\Extension\SearchExtension;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\SearchExtension\Command\SearchCommand;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Search\Adapter\TolerantParser\TolerantMatcher;
use Phpactor\Search\Model\Matcher;

class SearchExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(Matcher::class, function (Container $container) {
            return new TolerantMatcher($container->get(WorseReflectionExtension::SERVICE_PARSER));
        });

        $container->register(SearchCommand::class, function (Container $container) {
            return new SearchCommand(
                $container->get(Matcher::class),
                $container->get(SourceCodeFilesystemExtension::SERVICE_REGISTRY)
            );
        }, [
            ConsoleExtension::TAG_COMMAND => [
                'name' => 'search'
            ]
        ]);
    }

    public function configure(Resolver $schema): void
    {
    }
}
