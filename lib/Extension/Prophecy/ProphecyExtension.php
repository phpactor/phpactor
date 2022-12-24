<?php

namespace Phpactor\Extension\Prophecy;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\Prophecy\WorseReflection\ProphecyMemberContextResolver;
use Phpactor\Extension\Prophecy\WorseReflection\ProphecyStubLocator;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\WorseReflection\Core\SourceCodeLocator;

class ProphecyExtension implements OptionalExtension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(ProphecyMemberContextResolver::class, function (Container $container) {
            return new ProphecyMemberContextResolver();
        }, [ WorseReflectionExtension::TAG_MEMBER_TYPE_RESOLVER => []]);

        $container->register(SourceCodeLocator::class, function (Container $container) {
            return new ProphecyStubLocator();
        }, [ WorseReflectionExtension::TAG_SOURCE_LOCATOR => [
            'priority' => 290
        ]]);
    }

    public function configure(Resolver $schema): void
    {
    }

    public function name(): string
    {
        return 'prophecy';
    }
}
