<?php

namespace Phpactor\Extension\Laravel;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\CompletionWorse\CompletionWorseExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\LanguageServerCompletion\LanguageServerCompletionExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\Extension\Laravel\Completor\LaravelConfigCompletor;
use Phpactor\Extension\Laravel\Completor\LaravelContainerCompletor;
use Phpactor\Extension\Laravel\Completor\LaravelRouteCompletor;
use Phpactor\Extension\Laravel\Completor\LaravelViewCompletor;
use Phpactor\Extension\Laravel\DocumentManager\LaravelBladeInjector;
use Phpactor\Extension\Laravel\Handler\RefreshOnLaravelFileUpdateHandler;
use Phpactor\Extension\Laravel\Providers\LaravelModelPropertiesProvider;
use Phpactor\Extension\Laravel\Providers\LaravelQueryBuilderProvider;
use Phpactor\Extension\Laravel\ReferenceFinder\ViewReferenceFinder;
use Phpactor\Extension\Laravel\WorseReflection\LaravelContainerContextResolver;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\WorseReflection\Core\SourceCodeLocator\InternalLocator;

/**
 * @todo: goto definition for views can be done by string literal goto.
 */
class LaravelExtension implements OptionalExtension
{
    public const DEV_TOOLS_EXECUTABLE = 'laravel.devtools.path';
    public const PARAM_CONTAINER_COMPLETOR_ENABLED = 'completion_worse.completor.laravel.container.enabled';
    public const PARAM_VIEW_COMPLETOR_ENABLED = 'completion_worse.completor.laravel.view.enabled';
    public const PARAM_ROUTES_COMPLETOR_ENABLED = 'completion_worse.completor.laravel.routes.enabled';
    public const PARAM_CONFIG_COMPLETOR_ENABLED = 'completion_worse.completor.laravel.config.enabled';
    public const LARAVEL_MODEL_PROVIDER = 'laravel.model_provider';

    public function load(ContainerBuilder $container): void
    {
        $container->register(RefreshOnLaravelFileUpdateHandler::class, function (Container $container) {
            return new RefreshOnLaravelFileUpdateHandler(
                $container->get(LaravelContainerInspector::class),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => []]);

        $container->register(LaravelContainerInspector::class, function (Container $container) {
            $executablePath = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)
                                        ->resolve($container->getParameter(self::DEV_TOOLS_EXECUTABLE));
            $projectRoot = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)
                                     ->resolve('%project_root%');

            return new LaravelContainerInspector($executablePath, $projectRoot);
        });

        $container->register(LaravelContainerCompletor::class, function (Container $container) {
            return new LaravelContainerCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(LaravelContainerInspector::class)
            );
        }, [
            CompletionWorseExtension::TAG_TOLERANT_COMPLETOR => [
                'name' => 'laravel.container',
            ],
        ]);

        $container->register(LaravelConfigCompletor::class, function (Container $container) {
            return new LaravelConfigCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(LaravelContainerInspector::class)
            );
        }, [
            CompletionWorseExtension::TAG_TOLERANT_COMPLETOR => [
                'name' => 'laravel.config',
            ],
        ]);

        $container->register(LaravelViewCompletor::class, function (Container $container) {
            return new LaravelViewCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(LaravelContainerInspector::class)
            );
        }, [
            CompletionWorseExtension::TAG_TOLERANT_COMPLETOR => [
                'name' => 'laravel.view',
            ],
        ]);

        $container->register(LaravelRouteCompletor::class, function (Container $container) {
            return new LaravelRouteCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(LaravelContainerInspector::class)
            );
        }, [
            CompletionWorseExtension::TAG_TOLERANT_COMPLETOR => [
                'name' => 'laravel.routes',
            ],
        ]);

        $container->register(LaravelContainerContextResolver::class, function (Container $container) {
            return new LaravelContainerContextResolver(
                $container->get(LaravelContainerInspector::class)
            );
        }, [
            WorseReflectionExtension::TAG_MEMBER_TYPE_RESOLVER => [],
        ]);

        $container->register(LaravelBladeInjector::class, function (Container $container) {
            return new LaravelBladeInjector(
                $container->get(LaravelContainerInspector::class),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get('logging.logger')
            );
        }, [
            LanguageServerCompletionExtension::TAG_DOCUMENT_MODIFIER => []
        ]);

        $container->register(ViewReferenceFinder::class, function (Container $container) {
            return new ViewReferenceFinder(
                $container->get(LaravelContainerInspector::class)
            );
        }, [ ReferenceFinderExtension::TAG_DEFINITION_LOCATOR => []]);

        // Providers
        $container->register(LaravelModelPropertiesProvider::class, function (Container $container) {
            return new LaravelModelPropertiesProvider(
                $container->get(LaravelContainerInspector::class)
            );
        }, [ WorseReflectionExtension::TAG_MEMBER_PROVIDER => []]);

        $container->register(LaravelQueryBuilderProvider::class, function (Container $container) {
            return new LaravelQueryBuilderProvider(
                $container->get(LaravelContainerInspector::class)
            );
        }, [ WorseReflectionExtension::TAG_MEMBER_PROVIDER => []]);

        $container->register('laravel-stub-locator', function (Container $container) {
            return new InternalLocator([
                'LaravelBuilder' => __DIR__ . '/Stubs/LaravelRelationBuilderStub.php',
                'LaravelHasManyVirtualBuilder' => __DIR__ . '/Stubs/LaravelRelationBuilderStub.php',
                'LaravelBelongsToVirtualBuilder' => __DIR__ . '/Stubs/LaravelRelationBuilderStub.php',
                'LaravelBelongsToManyVirtualBuilder' => __DIR__ . '/Stubs/LaravelRelationBuilderStub.php',
                'LaravelQueryVirtualBuilder' => __DIR__ . '/Stubs/LaravelRelationBuilderStub.php',
            ]);
        }, [ WorseReflectionExtension::TAG_SOURCE_LOCATOR => [
            'priority' => 9999
        ]]);
    }


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::DEV_TOOLS_EXECUTABLE => 'laravel-dev-tools',
            self::PARAM_CONTAINER_COMPLETOR_ENABLED => true,
            self::PARAM_VIEW_COMPLETOR_ENABLED => true,
            self::PARAM_ROUTES_COMPLETOR_ENABLED => true,
            self::PARAM_CONFIG_COMPLETOR_ENABLED => true,
        ]);
        $schema->setDescriptions([
            self::DEV_TOOLS_EXECUTABLE => 'Path to the Laravel dev tools executable. By default it expects laravel-dev-tools to be in path.',
            self::PARAM_CONTAINER_COMPLETOR_ENABLED => 'Enable/disable the Laravel container completor - depends on Laravel extension being enabled',
            self::PARAM_VIEW_COMPLETOR_ENABLED => 'Enable/disable the Laravel view completor - depends on Laravel extension being enabled',
            self::PARAM_ROUTES_COMPLETOR_ENABLED => 'Enable/disable the Laravel routes completor - depends on Laravel extension being enabled',
            self::PARAM_CONFIG_COMPLETOR_ENABLED => 'Enable/disable the Laravel config completor - depends on Laravel extension being enabled',
        ]);
    }

    public function name(): string
    {
        return 'laravel';
    }
}
