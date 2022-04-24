<?php

namespace Phpactor\Extension\Completion;

use Phpactor\Completion\Core\ChainCompletor;
use Phpactor\Completion\Core\ChainSignatureHelper;
use Phpactor\Completion\Core\Completor\DedupeCompletor;
use Phpactor\Completion\Core\Completor\LimitingCompletor;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Completion\Core\TypedCompletorRegistry;
use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\Container;

class CompletionExtension implements Extension
{
    public const TAG_COMPLETOR = 'completion.completor';
    public const TAG_SHORT_DESC_FORMATTER = 'completion.short_desc.formatter';
    public const TAG_SIGNATURE_HELPER = 'language_server_completion.handler.signature_help';
    public const TAG_SNIPPET_FORMATTER = 'completion.snippet.formatter';
    public const SERVICE_REGISTRY = 'completion.registry';
    public const SERVICE_SHORT_DESC_FORMATTER = 'completion.short_desc.formatter';
    public const SERVICE_SIGNATURE_HELPER = 'completion.handler.signature_helper';
    public const SERVICE_SNIPPET_FORMATTER = 'completion.snippet.formatter';
    public const KEY_COMPLETOR_TYPES = 'types';
    public const PARAM_DEDUPE = 'completion.dedupe';
    public const PARAM_DEDUPE_MATCH_SHORT_DESCRIPTION = 'completion.dedupe_match_short_description';
    public const PARAM_LIMIT = 'completion.limit';

    
    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_DEDUPE => true,
            self::PARAM_DEDUPE_MATCH_SHORT_DESCRIPTION => true,
            self::PARAM_LIMIT => null,
        ]);
        $schema->setDescriptions([
            self::PARAM_DEDUPE => 'If results should be de-duplicated',
            self::PARAM_DEDUPE_MATCH_SHORT_DESCRIPTION => 'If ``' . self::PARAM_DEDUPE . '``, match on completion description intead of name',
            self::PARAM_LIMIT => 'Sets a limit on the number of completion suggestions for any request',
        ]);
    }

    
    public function load(ContainerBuilder $container): void
    {
        $this->registerCompletion($container);
    }

    private function registerCompletion(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_REGISTRY, function (Container $container) {
            $completors = [];
            foreach ($container->getServiceIdsForTag(self::TAG_COMPLETOR) as $serviceId => $attrs) {
                $types = $attrs[self::KEY_COMPLETOR_TYPES] ?? ['php'];
                foreach ($types as $type) {
                    if (!isset($completors[$type])) {
                        $completors[$type] = [];
                    }
                    $completors[$type][] = $container->get($serviceId);
                }
            }

            $mapped = [];
            foreach ($completors as $type => $completors) {
                $completors = new ChainCompletor($completors);
                if ($container->getParameter(self::PARAM_DEDUPE)) {
                    $completors = new DedupeCompletor(
                        $completors,
                        $container->getParameter(self::PARAM_DEDUPE_MATCH_SHORT_DESCRIPTION)
                    );
                }

                if ($limit = $container->getParameter(self::PARAM_LIMIT)) {
                    $completors = new LimitingCompletor($completors, $limit);
                }

                $mapped[(string)$type] = $completors;
            }

            return new TypedCompletorRegistry($mapped);
        });

        $container->register(self::SERVICE_SHORT_DESC_FORMATTER, function (Container $container) {
            $formatters = [];
            foreach (array_keys($container->getServiceIdsForTag(self::TAG_SHORT_DESC_FORMATTER)) as $serviceId) {
                $taggedFormatters = $container->get($serviceId);
                $taggedFormatters = is_array($taggedFormatters) ? $taggedFormatters : [ $taggedFormatters ];

                foreach ($taggedFormatters as $taggedFormatter) {
                    $formatters[] = $taggedFormatter;
                }
            }

            return new ObjectFormatter($formatters);
        });

        $container->register(self::SERVICE_SNIPPET_FORMATTER, function (Container $container) {
            $formatters = [];
            foreach (array_keys($container->getServiceIdsForTag(self::TAG_SNIPPET_FORMATTER)) as $serviceId) {
                $taggedFormatters = $container->get($serviceId);
                $taggedFormatters = is_array($taggedFormatters) ? $taggedFormatters : [ $taggedFormatters ];

                foreach ($taggedFormatters as $taggedFormatter) {
                    $formatters[] = $taggedFormatter;
                }
            }

            return new ObjectFormatter($formatters);
        });

        $container->register(self::SERVICE_SIGNATURE_HELPER, function (Container $container) {
            $helpers = [];

            $helper = null;
            foreach (array_keys($container->getServiceIdsForTag(self::TAG_SIGNATURE_HELPER)) as $serviceId) {
                $helpers[] = $container->get($serviceId);
            }

            return new ChainSignatureHelper(
                LoggingExtension::channelLogger($container, 'CT'),
                $helpers
            );
        });
    }
}
