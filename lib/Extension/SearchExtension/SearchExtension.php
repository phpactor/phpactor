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
use Phpactor\Search\Model\Filter\PassthroughMatchFilter;
use Phpactor\Search\Model\Matcher\PlaceholderMatcher;
use Phpactor\Search\Adapter\TolerantParser\Matcher\TokenEqualityMatcher;
use Phpactor\Search\Adapter\TolerantParser\TolerantMatchFinder;
use Phpactor\Search\Model\MatchFinder;
use Phpactor\Search\Model\Matcher\ChainMatcher;
use Phpactor\Search\Search;

class SearchExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(MatchFinder::class, function (Container $container) {
            return new TolerantMatchFinder(
                $container->get(WorseReflectionExtension::SERVICE_PARSER),
                new ChainMatcher(
                    new PlaceholderMatcher(),
                    new TokenEqualityMatcher(),
                )
            );
        });

        $container->register(Search::class, function (Container $container) {
            return new Search(
                $container->get(MatchFinder::class),
                new PassthroughMatchFilter()
            );
        });

        $container->register(SearchCommand::class, function (Container $container) {
            return new SearchCommand(
                $container->get(Search::class),
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
