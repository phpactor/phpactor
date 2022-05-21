<?php

namespace Phpactor\Extension\PhpSpec;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\PhpSpec\Provider\ObjectBehaviorMemberProvider;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;

class PhpSpecExtension implements Extension
{
    public const PARAM_ENABLED = 'phpspec.enabled';
    public const PARAM_SPEC_PREFIX = 'phpspec.spec_prefix';

    public function load(ContainerBuilder $container): void
    {
        $container->register(ObjectBehaviorMemberProvider::class, function (Container $container) {
            if (false === $container->getParameter(self::PARAM_ENABLED)) {
                return null;
            }

            return new ObjectBehaviorMemberProvider($container->getParameter(self::PARAM_SPEC_PREFIX));
        }, [ WorseReflectionExtension::TAG_MEMBER_PROVIDER => []]);
    }

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_ENABLED => false,
            self::PARAM_SPEC_PREFIX => 'spec',
        ]);
    }
}
