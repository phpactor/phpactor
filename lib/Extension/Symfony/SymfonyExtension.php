<?php

namespace Phpactor\Extension\Symfony;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\CompletionWorse\CompletionWorseExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\Symfony\Adapter\Symfony\XmlSymfonyContainerInspector;
use Phpactor\Extension\Symfony\Completor\SymfonyContainerCompletor;
use Phpactor\Extension\Symfony\Model\SymfonyContainerInspector;
use Phpactor\Extension\Symfony\WorseReflection\SymfonyContainerContextResolver;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;

class SymfonyExtension implements Extension
{
    const XML_PATH = 'symfony.xml_path';
    const PARAM_ENABLED = 'symfony.enabled';

    public function load(ContainerBuilder $container): void
    {
        $container->register(SymfonyContainerInspector::class, function (Container $container) {
            $xmlPath = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve($container->getParameter(self::XML_PATH));
            return new XmlSymfonyContainerInspector($xmlPath);
        });
        $container->register(SymfonyContainerCompletor::class, function (Container $container) {
            return new SymfonyContainerCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(SymfonyContainerInspector::class)
            );
        }, [
            CompletionWorseExtension::TAG_TOLERANT_COMPLETOR => [
                'name' => 'symfony',
            ],
        ]);
        $container->register(SymfonyContainerContextResolver::class, function (Container $container) {
            return new SymfonyContainerContextResolver(
                $container->get(SymfonyContainerInspector::class)
            );
        }, [
            WorseReflectionExtension::TAG_MEMBER_TYPE_RESOLVER => [
            ],
        ]);
    }


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_ENABLED => false,
            self::XML_PATH => '%project_root%/var/cache/dev/App_KernelDevDebugContainer.xml',
            'completion_worse.completor.symfony.enabled' => true,
        ]);
        $schema->setDescriptions([
            self::XML_PATH => 'Candidate paths to the development XML container dump',
        ]);
    }
}
