<?php

namespace Phpactor\Extension\Symfony;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Behat\Adapter\Symfony\SymfonyDiContextClassResolver;
use Phpactor\Extension\Behat\Adapter\Worse\WorseContextClassResolver;
use Phpactor\Extension\Behat\Adapter\Worse\WorseStepFactory;
use Phpactor\Extension\Behat\Behat\BehatConfig;
use Phpactor\Extension\Behat\Behat\ContextClassResolver;
use Phpactor\Extension\Behat\Behat\ContextClassResolver\ChainContextClassResolver;
use Phpactor\Extension\Behat\Behat\StepGenerator;
use Phpactor\Extension\Behat\Behat\StepParser;
use Phpactor\Extension\Behat\Completor\FeatureStepCompletor;
use Phpactor\Extension\Behat\ReferenceFinder\StepDefinitionLocator;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;

class SymfonyExtension implements Extension
{
    const PARAM_XML_PATHS = 'symfony.xml_path';
    const PARAM_ENABLED = 'symfony.enabled';

    public function load(ContainerBuilder $container): void
    {
    }


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_ENABLED => false,
            self::PARAM_XML_PATHS => [
                '%project_root%/var/cache/dev/App_KernelDevDebugContainer.xml',
            ],
        ]);
        $schema->setDescriptions([
            self::PARAM_XML_PATHS => 'Candidate paths to the development XML container dump',
        ]);
    }
}
