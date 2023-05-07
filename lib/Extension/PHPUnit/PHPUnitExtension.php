<?php

namespace Phpactor\Extension\PHPUnit;

use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Container;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\PHPUnit\CodeTransform\TestGenerator;
use Phpactor\Extension\PHPUnit\FrameWalker\AssertInstanceOfWalker;
use Phpactor\Extension\PHPUnit\LspCommand\GenerateTestMethodCommand;
use Phpactor\Extension\PHPUnit\Provider\GenerateTestMethodProvider;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\PHPUnit\CodeTransform\GenerateTestMethods;
use Phpactor\WorseReflection\Reflector;

class PHPUnitExtension implements OptionalExtension
{
    public function load(ContainerBuilder $container): void
    {
        $this->registerCommands($container);
        $this->registerServices($container);
        $this->registerWorseReflection($container);
        $this->registerCodeTransform($container);
    }


    public function configure(Resolver $schema): void
    {
    }

    public function name(): string
    {
        return 'phpunit';
    }

    public function registerCommands(ContainerBuilder $container): void
    {
        $container->register(
            GenerateTestMethodCommand::class,
            function (Container $container) {
                return new GenerateTestMethodCommand(
                    $container->get(ClientApi::class),
                    $container->expect(LanguageServerExtension::SERVICE_SESSION_WORKSPACE, Workspace::class),
                    $container->get(GenerateTestMethods::class)
                );
            },
            [
                LanguageServerExtension::TAG_COMMAND => [
                    'name' => GenerateTestMethodCommand::NAME
                ],
            ]
        );
    }

    private function registerServices(ContainerBuilder $container): void
    {
        $container->register(GenerateTestMethodProvider::class, function (Container $container) {
            return new GenerateTestMethodProvider(
                $container->get(GenerateTestMethods::class),
            );
        }, [
            LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []
        ]);

        $container->register(GenerateTestMethods::class, function (Container $container) {
            return new GenerateTestMethods(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class),
            );
        });
    }

    private function registerWorseReflection(ContainerBuilder $container): void
    {
        $container->register('phpunit.frame_walker.assert_instance_of', function (Container $container) {
            return new AssertInstanceOfWalker();
        }, [ WorseReflectionExtension::TAG_FRAME_WALKER => [] ]);
    }

    private function registerCodeTransform(ContainerBuilder $container): void
    {
        $container->register('phpunit.code_transform.test_generator', function (Container $container) {
            return new TestGenerator();
        }, [CodeTransformExtension::TAG_NEW_CLASS_GENERATOR => ['name' => 'phpunit']]);
    }
}
