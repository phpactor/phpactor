<?php

declare(strict_types=1);

namespace Phpactor\Extension\Pest;

use Phpactor\ComposerInspector\ComposerInspector;
use Phpactor\Configurator\Model\JsonConfig;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\CompletionWorse\CompletionWorseExtension;
use Phpactor\Extension\Configuration\ConfigurationExtension;
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
            function (Container $container) {
                return new PestCompletion($this->hasLaravelPlugin($container));
            },
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

    private function hasLaravelPlugin(Container $container): bool
    {
        $config = $container->expect(
            ConfigurationExtension::SERVICE_PHPACTOR_CONFIG_LOCAL,
            JsonConfig::class
        );
        $inspector = $container->get(ComposerInspector::class);

        return null !== $inspector->package('pestphp/pest-plugin-laravel');
    }
}
