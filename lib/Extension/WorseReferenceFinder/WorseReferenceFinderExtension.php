<?php

namespace Phpactor\Extension\WorseReferenceFinder;

use Microsoft\PhpParser\Parser;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\WorseReferenceFinder\TolerantVariableDefintionLocator;
use Phpactor\WorseReferenceFinder\WorsePlainTextClassDefinitionLocator;
use Phpactor\WorseReferenceFinder\WorseReflectionDefinitionLocator;
use Phpactor\WorseReferenceFinder\WorseReflectionTypeLocator;
use Phpactor\WorseReferenceFinder\TolerantVariableReferenceFinder;
use Phpactor\WorseReflection\Core\Cache;

class WorseReferenceFinderExtension implements Extension
{
    const PARAM_BREAK_CHARS = 'worse_reference_finder.plain_text_break_chars';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register('worse_reference_finder.definition_locator.reflection', function (Container $container) {
            return new WorseReflectionDefinitionLocator(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(Cache::class)
            );
        }, [ ReferenceFinderExtension::TAG_DEFINITION_LOCATOR => []]);
        $container->register('worse_reference_finder.type_locator.reflection', function (Container $container) {
            return new WorseReflectionTypeLocator(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        }, [ ReferenceFinderExtension::TAG_TYPE_LOCATOR => []]);

        $container->register('worse_reference_finder.definition_locator.plain_text_class', function (Container $container) {
            return new WorsePlainTextClassDefinitionLocator(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->getParameter(self::PARAM_BREAK_CHARS)
            );
        }, [ ReferenceFinderExtension::TAG_DEFINITION_LOCATOR => []]);

        $container->register('worse_reference_finder.definition_locator.variable', function (Container $container) {
            return new TolerantVariableDefintionLocator(
                new TolerantVariableReferenceFinder(
                    $container->get('worse_reflection.tolerant_parser'),
                    true
                )
            );
        }, [ ReferenceFinderExtension::TAG_DEFINITION_LOCATOR => []]);

        $container->register('worse_reference_finder.reference_finder.variable', function (Container $container) {
            return new TolerantVariableReferenceFinder(
                $container->get('worse_reflection.tolerant_parser'),
            );
        }, [ ReferenceFinderExtension::TAG_REFERENCE_FINDER => []]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_BREAK_CHARS => [' ', '"', '\'', '|', '%', '(', ')', '[', ']',':',"\r\n", "\n", "\r"]
        ]);
        $schema->setTypes([
            self::PARAM_BREAK_CHARS => 'array',
        ]);
    }
}
