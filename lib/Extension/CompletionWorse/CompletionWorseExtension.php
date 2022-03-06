<?php

namespace Phpactor\Extension\CompletionWorse;

use Phpactor\Completion\Bridge\TolerantParser\LimitingCompletor;
use Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder\NameSearcherCompletor;
use Phpactor\Completion\Bridge\TolerantParser\SourceCodeFilesystem\ScfClassCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\DoctrineAnnotationCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\KeywordCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseClassAliasCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseConstantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseConstructorCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseDeclaredClassCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseSignatureHelper;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\ClassFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\ConstantFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\EnumCaseFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\FunctionFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\InterfaceFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\MethodFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\ParametersFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\TraitFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\VariableFormatter;
use Phpactor\Completion\Bridge\TolerantParser\ChainTolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseClassMemberCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseFunctionCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseParameterCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseNamedParameterCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseLocalVariableCompletor;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\ParameterFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\PropertyFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\TypeFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\TypesFormatter;
use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\FunctionLikeSnippetFormatter;
use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\NameSearchResultClassSnippetFormatter;
use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\NameSearchResultFunctionSnippetFormatter;
use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\ParametersSnippetFormatter;
use Phpactor\Completion\Core\DocumentPrioritizer\DefaultResultPrioritizer;
use Phpactor\Completion\Core\DocumentPrioritizer\DocumentPrioritizer;
use Phpactor\Completion\Core\DocumentPrioritizer\SimilarityResultPrioritizer;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\Container;
use Phpactor\ReferenceFinder\NameSearcher;
use RuntimeException;

class CompletionWorseExtension implements Extension
{
    public const TAG_TOLERANT_COMPLETOR = 'completion_worse.tolerant_completor';

    public const PARAM_DISABLED_COMPLETORS = 'completion_worse.disabled_completors';
    public const PARAM_CLASS_COMPLETOR_LIMIT = 'completion_worse.completor.class.limit';
    public const PARAM_NAME_COMPLETION_PRIORITY = 'completion_worse.name_completion_priority';

    public const SERVICE_COMPLETOR_MAP = 'completion_worse.completor_map';
    public const SERVICE_COMPLETION_WORSE_SNIPPET_FORMATTERS = 'completion_worse.snippet.formatters';

    public const NAME_SEARCH_STRATEGY_PROXIMITY = 'proximity';
    public const NAME_SEARCH_STRATEGY_NONE = 'none';
    public const PARAM_EXPERIMENTAL = 'completion_worse.experimantal';
    public const PARAM_SNIPPETS = 'completion_worse.snippets';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $this->registerCompletion($container);
        $this->registerSignatureHelper($container);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_CLASS_COMPLETOR_LIMIT => 100,
            self::PARAM_DISABLED_COMPLETORS => [],
            self::PARAM_NAME_COMPLETION_PRIORITY => self::NAME_SEARCH_STRATEGY_PROXIMITY,
            self::PARAM_SNIPPETS => true,
            self::PARAM_EXPERIMENTAL => false,
        ]);
        $schema->setDescriptions([
            self::PARAM_SNIPPETS => 'Enable or disable completion snippets',
            self::PARAM_EXPERIMENTAL => 'Enable experimental functionality',
            self::PARAM_CLASS_COMPLETOR_LIMIT => 'Suggestion limit for the filesystem based SCF class_completor',
            self::PARAM_DISABLED_COMPLETORS => 'List of completors to disable (e.g. ``scf_class`` and ``declared_function``)',
            self::PARAM_NAME_COMPLETION_PRIORITY => <<<EOT
                Strategy to use when ordering completion results for classes and functions:

                - `proximity`: Classes and functions will be ordered by their proximity to the text document being edited.
                - `none`: No ordering will be applied.
                EOT
        ]);
    }

    private function registerCompletion(ContainerBuilder $container): void
    {
        $container->register(ChainTolerantCompletor::class, function (Container $container) {
            return new ChainTolerantCompletor(
                array_map(function (string $serviceId) use ($container) {
                    return $container->get($serviceId);
                }, $container->get(self::SERVICE_COMPLETOR_MAP)),
                $container->get('worse_reflection.tolerant_parser')
            );
        }, [ CompletionExtension::TAG_COMPLETOR => []]);

        $container->register(DoctrineAnnotationCompletor::class, function (Container $container) {
            return new DoctrineAnnotationCompletor(
                $container->get(NameSearcher::class),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        }, [ CompletionExtension::TAG_COMPLETOR => []]);

        $container->register(self::SERVICE_COMPLETOR_MAP, function (Container $container) {
            $completors = [];
            foreach ($container->getServiceIdsForTag(self::TAG_TOLERANT_COMPLETOR) as $serviceId => $attrs) {
                if (!isset($attrs['name'])) {
                    throw new RuntimeException(sprintf(
                        'Completor "%s" must declare an "name" attribute',
                        $serviceId
                    ));
                }

                $name = $attrs['name'];

                if (isset($completors[$name])) {
                    throw new RuntimeException(sprintf(
                        'Completor name "%s" (service ID "%s") already registered',
                        $name,
                        $serviceId
                    ));
                }

                $completors[$name] = $serviceId;
            }
            if ($diff = array_diff(
                $container->getParameter(self::PARAM_DISABLED_COMPLETORS),
                array_keys($completors)
            )) {
                throw new RuntimeException(sprintf(
                    'Unknown completors specified "%s", known completors: "%s"',
                    implode('", "', $diff),
                    implode('", "', array_keys($completors))
                ));
            }

            $enabledNames = array_diff(
                array_keys($completors),
                $container->getParameter(self::PARAM_DISABLED_COMPLETORS)
            );

            return array_filter(array_map(function (string $name, string $serviceId) use ($enabledNames) {
                if (!in_array($name, $enabledNames)) {
                    return false;
                }
                return $serviceId;
            }, array_keys($completors), $completors));
        });


        $container->register('completion_worse.completor.parameter', function (Container $container) {
            return new WorseParameterCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER)
            );
        }, [ self::TAG_TOLERANT_COMPLETOR => [
            'name' => 'worse_parameter',
        ]]);
        $container->register('completion_worse.completor.named_parameter', function (Container $container) {
            return new WorseNamedParameterCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER)
            );
        }, [ self::TAG_TOLERANT_COMPLETOR => [
            'name' => 'worse_named_parameter',
        ]]);
        $container->register('completion_worse.completor.constructor', function (Container $container) {
            return new WorseConstructorCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER)
            );
        }, [ self::TAG_TOLERANT_COMPLETOR => [
            'name' => 'worse_constructor',
        ]]);

        $container->register('completion_worse.completor.constructor', function (Container $container) {
            return new WorseConstructorCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER)
            );
        }, [ self::TAG_TOLERANT_COMPLETOR => [
            'name' => 'worse_constructor',
        ]]);

        $container->register('completion_worse.completor.tolerant.class_member', function (Container $container) {
            return new WorseClassMemberCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER),
                $container->get(CompletionExtension::SERVICE_SNIPPET_FORMATTER)
            );
        }, [ self::TAG_TOLERANT_COMPLETOR => [
            'name' => 'worse_class_member',
        ]]);

        $container->register('completion_worse.completor.tolerant.class', function (Container $container) {
            return new LimitingCompletor(new ScfClassCompletor(
                $container->get(SourceCodeFilesystemExtension::SERVICE_REGISTRY)->get('composer'),
                $container->get('class_to_file.file_to_class')
            ), $container->getParameter(self::PARAM_CLASS_COMPLETOR_LIMIT));
        }, [ self::TAG_TOLERANT_COMPLETOR => [
            'name' => 'scf_class',
        ]]);

        $container->register('completion_worse.completor.local_variable', function (Container $container) {
            return new WorseLocalVariableCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER)
            );
        }, [ self::TAG_TOLERANT_COMPLETOR => [
            'name' => 'worse_local_variable',
        ]]);

        $container->register('completion_worse.completor.function', function (Container $container) {
            return new WorseFunctionCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER),
                $container->get(CompletionExtension::SERVICE_SNIPPET_FORMATTER)
            );
        }, [ self::TAG_TOLERANT_COMPLETOR => [
            'name' => 'declared_function',
        ]]);

        $container->register('completion_worse.completor.constant', function (Container $container) {
            return new WorseConstantCompletor();
        }, [ self::TAG_TOLERANT_COMPLETOR => [
            'name' => 'declared_constant',
        ]]);

        $container->register('completion_worse.completor.class_alias', function (Container $container) {
            return new WorseClassAliasCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        }, [ self::TAG_TOLERANT_COMPLETOR => [
            'name' => 'worse_class_alias',
        ]]);

        $container->register('completion_worse.completor.declared_class', function (Container $container) {
            return new WorseDeclaredClassCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER)
            );
        }, [ self::TAG_TOLERANT_COMPLETOR => [
            'name' => 'declared_class',
        ]]);

        $container->register('completion_worse.completor.name_search', function (Container $container) {
            return new NameSearcherCompletor(
                $container->get(NameSearcher::class),
                new ObjectFormatter(
                    $container->get(self::SERVICE_COMPLETION_WORSE_SNIPPET_FORMATTERS)
                ),
                $container->get(DocumentPrioritizer::class)
            );
        }, [ self::TAG_TOLERANT_COMPLETOR => [
            'name' => 'name_search',
        ]]);

        $container->register('completion_worse.completor.keyword', function (Container $container) {
            return new KeywordCompletor(
            );
        }, [ self::TAG_TOLERANT_COMPLETOR => [
            'name' => 'keyword',
        ]]);

        $container->register(DocumentPrioritizer::class, function (Container $container) {
            switch ($container->getParameter(self::PARAM_NAME_COMPLETION_PRIORITY)) {
                case self::NAME_SEARCH_STRATEGY_PROXIMITY:
                    return new SimilarityResultPrioritizer();
                case self::NAME_SEARCH_STRATEGY_NONE:
                    return new DefaultResultPrioritizer();
                default:
                    throw new RuntimeException(sprintf(
                        'Unknown search priority strategy "%s", must be one of "%s"',
                        $container->getParameter(self::PARAM_NAME_COMPLETION_PRIORITY),
                        implode('", "', [
                            self::NAME_SEARCH_STRATEGY_PROXIMITY,
                            self::NAME_SEARCH_STRATEGY_NONE
                        ])
                    ));
            }
        });

        $container->register('completion_worse.short_desc.formatters', function (Container $container) {
            return [
                new TypeFormatter(),
                new TypesFormatter(),
                new MethodFormatter(),
                new ParameterFormatter(),
                new ParametersFormatter(),
                new ClassFormatter(),
                new PropertyFormatter(),
                new FunctionFormatter(),
                new VariableFormatter(),
                new InterfaceFormatter(),
                new TraitFormatter(),
                new ConstantFormatter(),
                new EnumCaseFormatter(),
            ];
        }, [ CompletionExtension::TAG_SHORT_DESC_FORMATTER => []]);

        $container->register(
            self::SERVICE_COMPLETION_WORSE_SNIPPET_FORMATTERS,
            function (Container $container) {
                $reflector = $container->get(WorseReflectionExtension::SERVICE_REFLECTOR);

                if (!$container->getParameter(self::PARAM_SNIPPETS)) {
                    return [];
                }

                $formatters = [
                    new FunctionLikeSnippetFormatter(),
                    new ParametersSnippetFormatter(),
                ];

                if ($container->getParameter(self::PARAM_EXPERIMENTAL)) {
                    $formatters = array_merge($formatters, [
                        new NameSearchResultFunctionSnippetFormatter($reflector),
                        new NameSearchResultClassSnippetFormatter($reflector),
                    ]);
                }

                return $formatters;
            },
            [ CompletionExtension::TAG_SNIPPET_FORMATTER => []]
        );
    }

    private function registerSignatureHelper(ContainerBuilder $container): void
    {
        $container->register('completion_worse.signature_helper', function (Container $container) {
            return new WorseSignatureHelper(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER)
            );
        }, [ CompletionExtension::TAG_SIGNATURE_HELPER => []]);
    }
}
