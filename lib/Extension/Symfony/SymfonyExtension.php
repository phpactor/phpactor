<?php

namespace Phpactor\Extension\Symfony;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\CompletionWorse\CompletionWorseExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\Symfony\Adapter\Symfony\XmlSymfonyContainerInspector;
use Phpactor\Extension\Symfony\Completor\SymfonyCompletor;
use Phpactor\Extension\Symfony\Model\SymfonyContainerInspector;
use Phpactor\Extension\Symfony\WorseReflection\SymfonyContainerContextResolver;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\MapResolver\Resolver;
use Phpactor\WorseReflection\Reflector;

class SymfonyExtension implements OptionalExtension
{
    const XML_PATH = 'symfony.xml_path';
    const PARAM_COMPLETOR_ENABLED = 'completion_worse.completor.symfony.enabled';
    const PARAM_ENABLED = 'symfony.enabled';
    public const PARAM_PUBLIC_SERVICES_ONLY = 'public_services_only';

    public function load(ContainerBuilder $container): void
    {
        $container->register(
            SymfonyContainerInspector::class,
            function (Container $container) {
                $xmlPath = $container->expect(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER, PathResolver::class)
                    ->resolve($container->parameter(self::XML_PATH)->string());
                return new XmlSymfonyContainerInspector(
                    $xmlPath,
                    $container->parameter(self::PARAM_PUBLIC_SERVICES_ONLY)->bool()
                );
            }
        );

        $container->register(
            SymfonyCompletor::class,
            function (Container $container) {
                return new SymfonyCompletor(
                    $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                    $container->get(SymfonyContainerInspector::class),
                    $container->get(QueryClient::class),
                );
            },
            [
                CompletionWorseExtension::TAG_TOLERANT_COMPLETOR => [
                    'name' => 'symfony',
                ],
            ]
        );

        $container->register(
            SymfonyContainerContextResolver::class,
            function (Container $container) {
                return new SymfonyContainerContextResolver(
                    $container->get(SymfonyContainerInspector::class)
                );
            },
            [
                WorseReflectionExtension::TAG_MEMBER_TYPE_RESOLVER => [],
            ]
        );
    }


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults(
            [
            self::XML_PATH => '%project_root%/var/cache/dev/App_KernelDevDebugContainer.xml',
            self::PARAM_COMPLETOR_ENABLED => true,
            self::PARAM_PUBLIC_SERVICES_ONLY => false,
            ]
        );
        $schema->setDescriptions(
            [
            self::XML_PATH => 'Path to the Symfony container XML dump file',
            self::PARAM_COMPLETOR_ENABLED => 'Enable/disable the Symfony completor - depends on Symfony extension being enabled',
            self::PARAM_PUBLIC_SERVICES_ONLY => 'Only consider public services when providing analysis for the service locator',
            ]
        );
    }

    public function name(): string
    {
        return 'symfony';
    }
}
