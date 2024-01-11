<?php

declare(strict_types=1);

namespace Phpactor\Extension\Pest;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\CompletionWorse\CompletionWorseExtension;
use Phpactor\Extension\Pest\Completion\PestCompletion;
use Phpactor\MapResolver\Resolver;

class PestExtension implements OptionalExtension
{
    const PARAM_COMPLETOR_ENABLED = 'completion_worse.completor.pest.enabled';
    public const PARAM_ENABLED = 'pest.enabled';

    public function load(ContainerBuilder $container): void
    {
        $container->register(
            'pest.completor',
            function (Container $container) {return new PestCompletion();},
            [
                CompletionWorseExtension::TAG_TOLERANT_COMPLETOR => [
                    'name' => 'pest',
                ],
            ]
        );
    }

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_COMPLETOR_ENABLED => true,
        ]);
    }

    public function name(): string
    {
        return 'pest';
    }
}
