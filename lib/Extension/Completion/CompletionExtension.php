<?php

namespace Phpactor\Extension\Completion;

use InvalidArgumentException;
use Phpactor\Completion\Core\ChainCompletor;
use Phpactor\Completion\Core\ChainSignatureHelper;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Completor\DedupeCompletor;
use Phpactor\Completion\Core\Completor\DocumentingCompletor;
use Phpactor\Completion\Core\Completor\LabelFormattingCompletor;
use Phpactor\Completion\Core\Completor\LimitingCompletor;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Completion\Core\LabelFormatter;
use Phpactor\Completion\Core\LabelFormatter\HelpfulLabelFormatter;
use Phpactor\Completion\Core\LabelFormatter\PassthruLabelFormatter;
use Phpactor\Completion\Core\SuggestionDocumentor;
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
    public const PARAM_DEDUPE_MATCH_FQN = 'completion.dedupe_match_fqn';
    public const PARAM_LIMIT = 'completion.limit';
    public const PARAM_LABEL_FORMATTER = 'completion.label_formatter';
    const LOGGER_CHANNEL = 'completion';

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_DEDUPE => true,
            self::PARAM_DEDUPE_MATCH_FQN => true,
            self::PARAM_LIMIT => null,
            self::PARAM_LABEL_FORMATTER => LabelFormatter::HELPFUL,
        ]);
        $schema->setDescriptions([
            self::PARAM_DEDUPE => 'If results should be de-duplicated',
            self::PARAM_DEDUPE_MATCH_FQN => 'If ``' . self::PARAM_DEDUPE . '``, consider the class FQN in addition to the completion suggestion',
            self::PARAM_LIMIT => 'Sets a limit on the number of completion suggestions for any request',
            self::PARAM_LABEL_FORMATTER => 'Definition of how to format entries in the completion list',
        ]);
        $schema->setEnums([
            self::PARAM_LABEL_FORMATTER => [
                LabelFormatter::HELPFUL,
                LabelFormatter::FQN,
            ]
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
                    $completor = $container->get($serviceId);
                    if (null === $completor) {
                        continue;
                    }
                    $completors[$type][] = $completor;
                }
            }

            $mapped = [];
            /** @var Completor[] $completors */
            foreach ($completors as $type => $completors) {
                $completors = new ChainCompletor($completors, LoggingExtension::channelLogger(
                    $container,
                    self::LOGGER_CHANNEL
                ));
                if ($container->parameter(self::PARAM_DEDUPE)->bool()) {
                    $completors = new DedupeCompletor(
                        $completors,
                        $container->parameter(self::PARAM_DEDUPE_MATCH_FQN)->bool()
                    );
                }

                $limit = $container->parameter(self::PARAM_LIMIT)->intOrNull();
                if (is_int($limit)) {
                    $completors = new LimitingCompletor($completors, $limit);
                }

                $completors = new LabelFormattingCompletor($completors, $container->get(LabelFormatter::class));
                if ($container->has(SuggestionDocumentor::class)) {
                    $completors = new DocumentingCompletor($completors, $container->get(SuggestionDocumentor::class));
                }

                $mapped[(string)$type] = $completors;
            }

            return new TypedCompletorRegistry($mapped);
        });

        $container->register(LabelFormatter::class, function (Container $container) {
            return match ($formatter = $container->parameter(self::PARAM_LABEL_FORMATTER)->string()) {
                LabelFormatter::HELPFUL => new HelpfulLabelFormatter(),
                LabelFormatter::FQN => new PassthruLabelFormatter(),
                default => throw new InvalidArgumentException('Unknown formatter type: ' . $formatter),
            };
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

            foreach (array_keys($container->getServiceIdsForTag(self::TAG_SIGNATURE_HELPER)) as $serviceId) {
                $helpers[] = $container->get($serviceId);
            }

            return new ChainSignatureHelper(
                LoggingExtension::channelLogger($container, self::LOGGER_CHANNEL),
                $helpers
            );
        });
    }
}
