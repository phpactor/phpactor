<?php

namespace Phpactor\Extension\CompletionWorse;

use Closure;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\Completion\Bridge\TolerantParser\DebugTolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\LimitingCompletor;
use Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder\AttributeCompletor;
use Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder\ClassLikeCompletor;
use Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder\ExpressionNameCompletor;
use Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder\TypeCompletor;
use Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder\UseNameCompletor;
use Phpactor\Completion\Bridge\TolerantParser\SourceCodeFilesystem\ScfClassCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TypeSuggestionProvider;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\DoctrineAnnotationCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\Helper\VariableCompletionHelper;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\KeywordCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\ImportedNameCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseConstantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseConstructorCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseDeclaredClassCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseSignatureHelper;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\DocblockCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseSubscriptCompletor;
use Phpactor\Completion\Bridge\WorseReflection\Completor\ContextSensitiveCompletor;
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
use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\FunctionLikeSnippetFormatter;
use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\NameSearchResultClassSnippetFormatter;
use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\NameSearchResultFunctionSnippetFormatter;
use Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter\ParametersSnippetFormatter;
use Phpactor\Completion\Bridge\WorseReflection\SuggestionDocumentor\WorseSuggestionDocumentor;
use Phpactor\Completion\Core\DocumentPrioritizer\DefaultResultPrioritizer;
use Phpactor\Completion\Core\DocumentPrioritizer\DocumentPrioritizer;
use Phpactor\Completion\Core\DocumentPrioritizer\SimilarityResultPrioritizer;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Completion\Core\SuggestionDocumentor;
use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\ObjectRenderer\ObjectRendererExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\Container;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Reflector;
use RuntimeException;

class CompletionWorseExtension implements Extension
{
    public const TAG_TOLERANT_COMPLETOR = 'completion_worse.tolerant_completor';
    public const PARAM_CLASS_COMPLETOR_LIMIT = 'completion_worse.completor.class.limit';
    public const PARAM_NAME_COMPLETION_PRIORITY = 'completion_worse.name_completion_priority';
    public const SERVICE_COMPLETOR_MAP = 'completion_worse.completor_map';
    public const SERVICE_COMPLETION_WORSE_SNIPPET_FORMATTERS = 'completion_worse.snippet.formatters';
    public const NAME_SEARCH_STRATEGY_PROXIMITY = 'proximity';
    public const NAME_SEARCH_STRATEGY_NONE = 'none';
    public const PARAM_EXPERIMENTAL = 'completion_worse.experimantal';
    public const PARAM_SNIPPETS = 'completion_worse.snippets';
    public const PARAM_DEBUG = 'completion_worse.debug';

    public function load(ContainerBuilder $container): void
    {
        $this->registerCompletion($container);
        $this->registerSignatureHelper($container);
    }


    public function configure(Resolver $schema): void
    {
        $completors = array_merge($this->getOtherCompletors(), $this->getTolerantCompletors());
        $defaults = array_combine(array_map(
            fn (string $key) => $this->completorEnabledKey($key),
            array_keys($completors)
        ), array_map(
            fn (string $key) => true,
            array_keys($completors)
        ));

        $defaults['completion_worse.completor.constant.enabled'] = false;

        $schema->setDefaults(array_merge($defaults, [
            self::PARAM_CLASS_COMPLETOR_LIMIT => 100,
            self::PARAM_NAME_COMPLETION_PRIORITY => self::NAME_SEARCH_STRATEGY_PROXIMITY,
            self::PARAM_SNIPPETS => true,
            self::PARAM_EXPERIMENTAL => false,
            self::PARAM_DEBUG => false,
        ]));

        $descriptions = array_combine(array_map(
            fn (string $key) => sprintf('completion_worse.completor.%s.enabled', $key),
            array_keys($completors)
        ), array_map(
            fn (string $key, array $pair) => sprintf(
                "Enable or disable the ``%s`` completor.\n\n%s.",
                $key,
                $pair[0]
            ),
            array_keys($completors),
            $completors
        ));

        $schema->setDescriptions(array_merge($descriptions, [
            self::PARAM_DEBUG => 'Include debug info in completion results',
            self::PARAM_SNIPPETS => 'Enable or disable completion snippets',
            self::PARAM_EXPERIMENTAL => 'Enable experimental functionality',
            self::PARAM_CLASS_COMPLETOR_LIMIT => 'Suggestion limit for the filesystem based SCF class_completor',
            self::PARAM_NAME_COMPLETION_PRIORITY => <<<EOT
                Strategy to use when ordering completion results for classes and functions:

                - `proximity`: Classes and functions will be ordered by their proximity to the text document being edited.
                - `none`: No ordering will be applied.
                EOT
        ]));
    }

    private function registerCompletion(ContainerBuilder $container): void
    {
        foreach ($this->getTolerantCompletors() as $name => [$_, $completor]) {
            $container->register(sprintf('worse_completion.completor.%s', $name), $completor, [
                self::TAG_TOLERANT_COMPLETOR => [ 'name' => $name ]
            ]);
        }
        foreach ($this->getOtherCompletors() as $name => [$_, $completor]) {
            $container->register(sprintf('worse_completion.completor.%s', $name), $completor, [
                CompletionExtension::TAG_COMPLETOR => [ 'name' => $name ]
            ]);
        }

        $container->register(SuggestionDocumentor::class, function (Container $container) {
            return new WorseSuggestionDocumentor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(ObjectRendererExtension::SERVICE_MARKDOWN_RENDERER)
            );
        });

        $container->register(TypeSuggestionProvider::class, function (Container $container) {
            return new TypeSuggestionProvider(
                $container->get(NameSearcher::class)
            );
        });

        $container->register(ChainTolerantCompletor::class, function (Container $container) {
            return new ChainTolerantCompletor(
                array_filter(array_map(function (string $serviceId) use ($container) {
                    if ($container->parameter(self::PARAM_DEBUG)->bool()) {
                        return new DebugTolerantCompletor($container->get($serviceId));
                    }
                    return $container->get($serviceId) ?? false;
                }, $container->get(self::SERVICE_COMPLETOR_MAP))),
                $container->expect('worse_reflection.tolerant_parser', AstProvider::class)
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

                if (false === $container->getParameter($this->completorEnabledKey($name))) {
                    continue;
                }
                $completors[$name] = $serviceId;
            }

            return $completors;
        });

        $container->register(DocumentPrioritizer::class, function (Container $container) {
            $priority = $container->getParameter(self::PARAM_NAME_COMPLETION_PRIORITY);
            return match ($priority) {
                self::NAME_SEARCH_STRATEGY_PROXIMITY => new SimilarityResultPrioritizer(),
                self::NAME_SEARCH_STRATEGY_NONE => new DefaultResultPrioritizer(),
                default => throw new RuntimeException(sprintf(
                    'Unknown search priority strategy "%s", must be one of "%s"',
                    $priority,
                    implode('", "', [
                        self::NAME_SEARCH_STRATEGY_PROXIMITY,
                        self::NAME_SEARCH_STRATEGY_NONE
                    ])
                )),
            };
        });

        $container->register('completion_worse.short_desc.formatters', function (Container $container) {
            return [
                new TypeFormatter(),
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

                if (!$container->parameter(self::PARAM_SNIPPETS)->bool()) {
                    return [];
                }

                $formatters = [
                    new FunctionLikeSnippetFormatter(),
                    new ParametersSnippetFormatter(),
                ];

                if ($container->parameter(self::PARAM_EXPERIMENTAL)->bool()) {
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
    /**
     * @return array<string,array{string,Closure(Container): mixed}>
     */
    private function getOtherCompletors(): array
    {
        return [
            'doctrine_annotation' => [
                'Completion for annotations provided by the Doctrine annotation library',
                function (Container $container) {
                    return new DoctrineAnnotationCompletor(
                        $container->get(NameSearcher::class),
                        $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                        $container->expect(WorseReflectionExtension::SERVICE_PARSER, AstProvider::class)
                    );
                },
            ],
        ];
    }

    /**
     * @return array<string,array{string,Closure(Container): mixed}>
     */
    private function getTolerantCompletors(): array
    {
        return [
            'imported_names' => [
                'Completion for names imported into the current namespace',
                function (Container $container) {
                    return $this->contextCompletor($container, new ImportedNameCompletor(
                    ));
                },
            ],
            'worse_parameter' => [
                'Completion for method or function parameters',
                function (Container $container) {
                    return new WorseParameterCompletor(
                        $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                        $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER)
                    );
                },
            ],
            'named_parameter' => [
                'Completion for named parameters',
                function (Container $container) {
                    return new WorseNamedParameterCompletor(
                        $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                        $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER)
                    );
                },
            ],
            'constructor' => [
                'Completion for constructors',
                function (Container $container) {
                    return new WorseConstructorCompletor(
                        $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                        $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER)
                    );
                },
            ],
            'class_member' => [
                'Completion for class members',
                function (Container $container) {
                    return new WorseClassMemberCompletor(
                        $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                        $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER),
                        $container->get(CompletionExtension::SERVICE_SNIPPET_FORMATTER),
                        $container->get(ObjectRendererExtension::SERVICE_MARKDOWN_RENDERER)
                    );
                },
            ],
            'scf_class' => [
                'Brute force completion for class names (not recommended)',
                function (Container $container) {
                    return $this->limitCompletor($container, new ScfClassCompletor(
                        $container->get(SourceCodeFilesystemExtension::SERVICE_REGISTRY)->get('composer'),
                        $container->get('class_to_file.file_to_class')
                    ));
                },
            ],
            'local_variable' => [
                'Completion for local variables',
                function (Container $container) {
                    return new WorseLocalVariableCompletor(
                        new VariableCompletionHelper(
                            $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                        ),
                        $container->expect(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER, ObjectFormatter::class)
                    );
                },
            ],
            'subscript' => [
                'Completion for subscript (array access from array shapes)',
                function (Container $container) {
                    return new WorseSubscriptCompletor(
                        $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, SourceCodeReflector::class),
                    );
                },
            ],
            'declared_function' => [
                'Completion for functions defined in the Phpactor runtime',
                function (Container $container) {
                    return new WorseFunctionCompletor(
                        $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                        $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER),
                        $container->get(CompletionExtension::SERVICE_SNIPPET_FORMATTER)
                    );
                },
            ],
            'declared_constant' => [
                'Completion for constants defined in the Phpactor runtime',
                function (Container $container) {
                    return new WorseConstantCompletor();
                },
            ],
            'declared_class' => [
                'Completion for classes defined in the Phpactor runtime',
                function (Container $container) {
                    return new WorseDeclaredClassCompletor(
                        $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                        $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER)
                    );
                },
            ],
            'expression_name_search' => [
                'Completion for class names, constants and functions at expression positions that are located in the index',
                function (Container $container) {
                    return $this->contextCompletor($container, $this->limitCompletor($container, new ExpressionNameCompletor(
                        $container->get(NameSearcher::class),
                        new ObjectFormatter(
                            $container->get(self::SERVICE_COMPLETION_WORSE_SNIPPET_FORMATTERS)
                        ),
                        $container->get(DocumentPrioritizer::class)
                    )));
                },
            ],
            'use' => [
                'Completion for use imports',
                function (Container $container) {
                    return $this->limitCompletor($container, new UseNameCompletor(
                        $container->get(NameSearcher::class),
                        $container->get(DocumentPrioritizer::class)
                    ));
                },
            ],
            'attribute' => [
                'Completion for attribute class names',
                function (Container $container) {
                    return $this->limitCompletor($container, new AttributeCompletor(
                        $container->get(NameSearcher::class),
                        $container->get(DocumentPrioritizer::class)
                    ));
                },
            ],
            'class_like' => [
                'Completion for class like contexts',
                function (Container $container) {
                    return $this->limitCompletor($container, new ClassLikeCompletor(
                        $container->get(NameSearcher::class),
                        $container->get(DocumentPrioritizer::class)
                    ));
                },
            ],
            'type' => [
                'Completion for scalar types',
                function (Container $container) {
                    return $this->limitCompletor($container, new TypeCompletor(
                        $container->get(TypeSuggestionProvider::class)
                    ));
                },
            ],
            'keyword' => [
                'Completion for keywords (not very accurate)',
                function (Container $container) {
                    return new KeywordCompletor();
                },
            ],
            'docblock' => [
                'Docblock completion',
                function (Container $container) {
                    return new DocblockCompletor(
                        $container->get(TypeSuggestionProvider::class),
                        $container->get(WorseReflectionExtension::SERVICE_PARSER)
                    );
                },
            ],
        ];
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

    private function completorEnabledKey(string $key): string
    {
        return sprintf('completion_worse.completor.%s.enabled', $key);
    }

    private function limitCompletor(Container $container, TolerantCompletor $completor): TolerantCompletor
    {
        $limit = $container->parameter(self::PARAM_CLASS_COMPLETOR_LIMIT)->int();

        return new LimitingCompletor($completor, $limit);
    }

    private function contextCompletor(Container $container, TolerantCompletor $tolerantCompletor): TolerantCompletor
    {
        return new ContextSensitiveCompletor(
            $tolerantCompletor,
            $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class)
        );
    }
}
