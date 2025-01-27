<?php

namespace Phpactor\Extension\CodeTransform;

use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer74;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer80;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer81;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer82;
use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRendererFactory;
use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeBuilder\Adapter\Twig\TwigExtension;
use Phpactor\CodeBuilder\Adapter\Twig\TwigRenderer;
use Phpactor\CodeBuilder\Domain\TemplatePathResolver\PhpVersionPathResolver;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TolerantUpdater;
use Phpactor\CodeTransform\Adapter\DocblockParser\ParserDocblockUpdater;
use Phpactor\CodeTransform\Adapter\Native\GenerateNew\ClassGenerator;
use Phpactor\CodeTransform\Adapter\TolerantParser\ClassToFile\Transformer\ClassNameFixerTransformer;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantChangeVisiblity;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantImportName;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantExtractExpression;
use Phpactor\CodeTransform\Adapter\WorseReflection\GenerateFromExisting\InterfaceFromExistingGenerator;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantRenameVariable;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantHereDoc;
use Phpactor\CodeTransform\Adapter\WorseReflection\Helper\WorseMissingMemberFinder;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseExtractMethod;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseFillMatchArms;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseFillObject;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateConstructor;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateMutator;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseReplaceQualifierWithImport;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateDecorator;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseOverrideMethod;
use Phpactor\CodeTransform\Adapter\WorseReflection\Helper\WorseInterestingOffsetFinder;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateAccessor;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateMember;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\AddMissingProperties;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseExtractConstant;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\ImplementContracts;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\CompleteConstructor;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\RemoveUnusedImportsTransformer;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\UpdateDocblockGenericTransformer;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\UpdateDocblockParamsTransformer;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\UpdateDocblockReturnTransformer;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\UpdateReturnTypeTransformer;
use Phpactor\CodeTransform\CodeTransform;
use Phpactor\CodeTransform\Domain\DocBlockUpdater;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\CodeTransform\Domain\Helper\InterestingOffsetFinder;
use Phpactor\CodeTransform\Domain\Helper\MissingMemberFinder;
use Phpactor\CodeTransform\Domain\Refactor\ChangeVisiblity;
use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\CodeTransform\Domain\Refactor\ExtractExpression;
use Phpactor\CodeTransform\Domain\Refactor\ExtractMethod;
use Phpactor\CodeTransform\Domain\Refactor\GenerateConstructor;
use Phpactor\CodeTransform\Domain\Refactor\GenerateDecorator;
use Phpactor\CodeTransform\Domain\Refactor\ImportName;
use Phpactor\CodeTransform\Domain\Refactor\OverrideMethod;
use Phpactor\CodeTransform\Domain\Refactor\RenameVariable;
use Phpactor\CodeTransform\Domain\Refactor\GenerateMember;
use Phpactor\CodeTransform\Domain\Refactor\ReplaceQualifierWithImport;
use Phpactor\DocblockParser\DocblockParser;
use Phpactor\Extension\CodeTransform\Rpc\TransformHandler;
use Phpactor\Extension\CodeTransform\Rpc\ClassNewHandler;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\CodeTransform\Rpc\ClassInflectHandler;
use Phpactor\Extension\Php\Model\PhpVersionResolver;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\WorseReflection\Reflector;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Phpactor\CodeTransform\Domain\Transformers;
use Twig\Loader\ChainLoader;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;
use RuntimeException;

class CodeTransformExtension implements Extension
{
    public const TAG_FROM_EXISTING_GENERATOR = 'code_transform.from_existing_generator';
    public const TAG_TRANSFORMER = 'code_transform.transformer';
    public const TAG_NEW_CLASS_GENERATOR = 'code_transform.new_class_generator';
    public const SERVICE_CLASS_GENERATORS = 'code_transform.new_class_generators';
    public const SERVICE_CLASS_INFLECTORS = 'code_transform.from_existing_generators';
    public const PARAM_NEW_CLASS_VARIANTS = 'code_transform.class_new.variants';
    public const PARAM_TEMPLATE_PATHS = 'code_transform.template_paths';
    public const PARAM_INDENTATION = 'code_transform.indentation';
    public const PARAM_GENERATE_ACCESSOR_PREFIX = 'code_transform.refactor.generate_accessor.prefix';
    public const PARAM_GENERATE_ACCESSOR_UPPER_CASE_FIRST = 'code_transform.refactor.generate_accessor.upper_case_first';
    public const PARAM_GENERATE_MUTATOR_PREFIX = 'code_transform.refactor.generate_mutator.prefix';
    public const PARAM_GENERATE_MUTATOR_UPPER_CASE_FIRST = 'code_transform.refactor.generate_mutator.upper_case_first';
    public const PARAM_GENERATE_MUTATOR_FLUENT = 'code_transform.refactor.generate_mutator.fluent';
    public const PARAM_IMPORT_GLOBALS = 'code_transform.import_globals';
    public const PARAM_OBJECT_FILL_NAMED = 'code_transform.refactor.object_fill.named_parameters';
    public const PARAM_OBJECT_FILL_HINT = 'code_transform.refactor.object_fill.hint';
    private const APP_TEMPLATE_PATH = '%application_root%/templates/code';


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_NEW_CLASS_VARIANTS => [],
            self::PARAM_TEMPLATE_PATHS => [ // Ordered by priority
                '%project_config%/templates',
                '%config%/templates',
            ],
            self::PARAM_INDENTATION => '    ',
            self::PARAM_GENERATE_ACCESSOR_PREFIX => '',
            self::PARAM_GENERATE_ACCESSOR_UPPER_CASE_FIRST => false,
            self::PARAM_GENERATE_MUTATOR_PREFIX => 'set',
            self::PARAM_GENERATE_MUTATOR_UPPER_CASE_FIRST => true,
            self::PARAM_GENERATE_MUTATOR_FLUENT => false,
            self::PARAM_IMPORT_GLOBALS => false,
            self::PARAM_OBJECT_FILL_HINT => true,
            self::PARAM_OBJECT_FILL_NAMED => true,
        ]);
        $schema->setDescriptions([
            self::PARAM_NEW_CLASS_VARIANTS => 'Variants which should be suggested when class-create is invoked',
            self::PARAM_TEMPLATE_PATHS => 'Paths in which to look for code templates',
            self::PARAM_INDENTATION => 'Indentation chars to use in code generation and transformation',
            self::PARAM_GENERATE_ACCESSOR_PREFIX => 'Prefix to use for generated accessors',
            self::PARAM_GENERATE_ACCESSOR_UPPER_CASE_FIRST => 'If the first letter of a generated accessor should be made uppercase',
            self::PARAM_GENERATE_MUTATOR_PREFIX => 'Prefix to use for generated mutators',
            self::PARAM_GENERATE_MUTATOR_UPPER_CASE_FIRST => 'If the first letter of a generated mutator should be made uppercase',
            self::PARAM_GENERATE_MUTATOR_FLUENT => 'If the mutator should be fluent',
            self::PARAM_IMPORT_GLOBALS => 'Import functions even if they are in the global namespace',
            self::PARAM_OBJECT_FILL_NAMED => 'Object fill refactoring: use named parameters',
            self::PARAM_OBJECT_FILL_HINT => 'Object fill refactoring: show hint as a comment',
        ]);
    }

    public function load(ContainerBuilder $container): void
    {
        $this->registerTransformers($container);
        $this->registerGenerators($container);
        $this->registerFinders($container);

        if (class_exists(RpcExtension::class)) {
            // this shouldn't be here
            $this->registerRpc($container);
        }

        $this->registerUpdater($container);
        $this->registerRefactorings($container);
        $this->registerTransformerImplementations($container);
        $this->registerRenderer($container);
        $this->registerGeneratorImplementations($container);
    }

    private function registerTransformers(ContainerBuilder $container): void
    {
        $container->register(CodeTransform::class, function (Container $container) {
            return CodeTransform::fromTransformers($container->get('code_transform.transformers'));
        });

        $container->register('code_transform.transformers', function (Container $container) {
            $transformers = [];
            foreach ($container->getServiceIdsForTag(self::TAG_TRANSFORMER) as $serviceId => $attrs) {
                $transformers[$attrs['name']] = $container->get($serviceId);
            }

            return Transformers::fromArray($transformers);
        });
    }

    private function registerGenerators(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_CLASS_GENERATORS, function (Container $container) {
            $generators = [];
            foreach ($container->getServiceIdsForTag(self::TAG_NEW_CLASS_GENERATOR) as $serviceId => $attrs) {
                $generator = $container->get($serviceId);

                // if the tagged "service" is an array, then assume it's an
                // array of class generators and move on.
                if (is_array($generator)) {
                    $generators = array_merge($generators, $generator);
                    continue;
                }

                $this->assertNameAttribute($attrs, $serviceId);
                $generators[$attrs['name']] = $generator;
            }

            return Generators::fromArray($generators);
        });

        $container->register(self::SERVICE_CLASS_INFLECTORS, function (Container $container) {
            $generators = [];
            foreach ($container->getServiceIdsForTag(self::TAG_FROM_EXISTING_GENERATOR) as $serviceId => $attrs) {
                $this->assertNameAttribute($attrs, $serviceId);
                $generators[$attrs['name']] = $container->get($serviceId);
            }

            return Generators::fromArray($generators);
        });
    }

    private function registerRefactorings(ContainerBuilder $container): void
    {
        $container->register(ReplaceQualifierWithImport::class, function (Container $container) {
            return new WorseReplaceQualifierWithImport(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(BuilderFactory::class),
                $container->get(Updater::class)
            );
        });

        $container->register(ExtractConstant::class, function (Container $container) {
            return new WorseExtractConstant(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class)
            );
        });

        $container->register(GenerateDecorator::class, function (Container $container) {
            return new WorseGenerateDecorator(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class),
            );
        });

        $container->register(GenerateMember::class, function (Container $container) {
            return new WorseGenerateMember(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(BuilderFactory::class),
                $container->get(Updater::class)
            );
        });

        $container->register('code_transform.generate_accessor', function (Container $container) {
            return new WorseGenerateAccessor(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class),
                $container->parameter(self::PARAM_GENERATE_ACCESSOR_PREFIX)->string(),
                $container->parameter(self::PARAM_GENERATE_ACCESSOR_UPPER_CASE_FIRST)->bool()
            );
        });

        $container->register('code_transform.generate_mutator', function (Container $container) {
            return new WorseGenerateMutator(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class),
                $container->parameter(self::PARAM_GENERATE_MUTATOR_PREFIX)->string(),
                $container->parameter(self::PARAM_GENERATE_MUTATOR_UPPER_CASE_FIRST)->bool(),
                $container->parameter(self::PARAM_GENERATE_MUTATOR_FLUENT)->bool()
            );
        });

        $container->register(RenameVariable::class, function (Container $container) {
            return new TolerantRenameVariable();
        });

        $container->register(OverrideMethod::class, function (Container $container) {
            return new WorseOverrideMethod(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(BuilderFactory::class),
                $container->get(Updater::class)
            );
        });

        $container->register(ExtractMethod::class, function (Container $container) {
            return new WorseExtractMethod(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(BuilderFactory::class),
                $container->get(Updater::class)
            );
        });

        $container->register(ExtractExpression::class, function (Container $container) {
            return new TolerantExtractExpression();
        });

        $container->register(ImportName::class, function (Container $container) {
            return new TolerantImportName(
                $container->get(Updater::class),
                $container->get(WorseReflectionExtension::SERVICE_PARSER),
                $container->parameter(self::PARAM_IMPORT_GLOBALS)->bool(),
            );
        });
        $container->register(WorseFillObject::class, function (Container $container) {
            return new WorseFillObject(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->expect(WorseReflectionExtension::SERVICE_PARSER, Parser::class),
                $container->get(Updater::class),
                $container->parameter(self::PARAM_OBJECT_FILL_NAMED)->bool(),
                $container->parameter(self::PARAM_OBJECT_FILL_HINT)->bool(),
            );
        });
        $container->register(WorseFillMatchArms::class, function (Container $container) {
            return new WorseFillMatchArms(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->expect(WorseReflectionExtension::SERVICE_PARSER, Parser::class),
            );
        });
        $container->register(TolerantHereDoc::class, function (Container $container) {
            return new TolerantHereDoc(
                $container->expect(WorseReflectionExtension::SERVICE_PARSER, Parser::class),
            );
        });

        $container->register(GenerateConstructor::class, function (Container $container) {
            return new WorseGenerateConstructor(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(BuilderFactory::class),
                $container->get(Updater::class),
                $container->get(WorseReflectionExtension::SERVICE_PARSER),
            );
        });

        $container->register(ChangeVisiblity::class, function (Container $container) {
            return new TolerantChangeVisiblity();
        });
    }

    private function registerGeneratorImplementations(ContainerBuilder $container): void
    {
        $container->register('code_transform_extra.class_generator.variants', function (Container $container) {
            $generators = [
                'default' => new ClassGenerator($container->get('code_transform.renderer')),
                'interface' => new ClassGenerator($container->get('code_transform.renderer'), 'interface'),
                'trait' => new ClassGenerator($container->get('code_transform.renderer'), 'trait'),
                'enum' => new ClassGenerator($container->get('code_transform.renderer'), 'enum'),
            ];
            foreach ($container->getParameter(self::PARAM_NEW_CLASS_VARIANTS) as $variantName => $variant) {
                $generators[$variantName] = new ClassGenerator($container->get('code_transform.renderer'), $variant);
            }

            return $generators;
        }, [ CodeTransformExtension::TAG_NEW_CLASS_GENERATOR => [] ]);

        $container->register('code_transform_extra.from_existing_generator', function (Container $container) {
            return new InterfaceFromExistingGenerator(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get('code_transform.renderer')
            );
        }, [ CodeTransformExtension::TAG_FROM_EXISTING_GENERATOR => [
            'name' => 'interface'
        ] ]);
    }

    private function registerUpdater(ContainerBuilder $container): void
    {
        $container->register(Updater::class, function (Container $container) {
            return new TolerantUpdater(
                $container->get('code_transform.renderer'),
                $container->get(TextFormat::class),
                $container->get(WorseReflectionExtension::SERVICE_PARSER)
            );
        });
        $container->register(BuilderFactory::class, function (Container $container) {
            return new WorseBuilderFactory($container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class));
        });

        $container->register(DocBlockUpdater::class, function (Container $container) {
            return new ParserDocblockUpdater(DocblockParser::create(), $container->get(TextFormat::class));
        });
    }

    private function registerFinders(ContainerBuilder $container): void
    {
        $container->register(InterestingOffsetFinder::class, function (Container $container) {
            return new WorseInterestingOffsetFinder(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class)
            );
        });
        $container->register(MissingMemberFinder::class, function (Container $container) {
            return new WorseMissingMemberFinder(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class)
            );
        });
    }

    private function registerRenderer(ContainerBuilder $container): void
    {
        $container->register('code_transform.twig_loader', function (Container $container) {
            $resolver = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER);
            $loader = new ChainLoader();
            $templatePaths = $container->getParameter(self::PARAM_TEMPLATE_PATHS);
            $templatePaths[] = self::APP_TEMPLATE_PATH;

            $resolvedTemplatePaths = array_map(function (string $path) use ($resolver) {
                return $resolver->resolve($path);
            }, $templatePaths);

            $phpVersion = $container->get(PhpVersionResolver::class)->resolve();
            $paths = (new PhpVersionPathResolver($phpVersion))->resolve($resolvedTemplatePaths);

            foreach ($paths as $path) {
                $loader->addLoader(new FilesystemLoader($path));
            }

            return $loader;
        });

        $container->register('code_transform.renderer', function (Container $container) {
            $twig = new Environment($container->get('code_transform.twig_loader'), [
                'strict_variables' => true,
                'autoescape' => false,
            ]);
            $renderer = new TwigRenderer($twig);
            $twig->addExtension(new TwigExtension(
                $container->get(TextFormat::class),
                $container->get(WorseTypeRenderer::class)
            ));

            return $renderer;
        });

        $container->register(WorseTypeRenderer::class, function (Container $container) {
            $version = $container->get(PhpVersionResolver::class);
            assert($version instanceof PhpVersionResolver);
            $version = $version->resolve();
            return (new WorseTypeRendererFactory([
                '7.4' => new WorseTypeRenderer74(),
                '8.0' => new WorseTypeRenderer80(),
                '8.1' => new WorseTypeRenderer81(),
                '8.2' => new WorseTypeRenderer82(),
            ]))->rendererFor($version);
        });

        $container->register(TextFormat::class, function (Container $container) {
            return new TextFormat($container->parameter(self::PARAM_INDENTATION)->string());
        });
    }

    private function assertNameAttribute($attrs, $serviceId): void
    {
        if (!isset($attrs['name'])) {
            throw new RuntimeException(sprintf(
                'Generator "%s" must be registered with the "name" tag',
                $serviceId
            ));
        }
    }

    private function registerRpc(ContainerBuilder $container): void
    {
        $container->register('code_transform.rpc.handler.class_inflect', function (Container $container) {
            return new ClassInflectHandler(
                $container->get(self::SERVICE_CLASS_INFLECTORS),
                $container->get(ClassToFileExtension::SERVICE_CONVERTER)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ClassInflectHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.class_new', function (Container $container) {
            return new ClassNewHandler(
                $container->get(self::SERVICE_CLASS_GENERATORS),
                $container->get(ClassToFileExtension::SERVICE_CONVERTER)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ClassNewHandler::NAME] ]);


        $container->register('code_transform.rpc.handler.transform', function (Container $container) {
            return new TransformHandler(
                $container->get(CodeTransform::class)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => TransformHandler::NAME] ]);
    }

    private function registerTransformerImplementations(ContainerBuilder $container): void
    {
        $container->register('code_transform.transformer.complete_constructor_private', function (Container $container) {
            return new CompleteConstructor(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class),
                'private',
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'complete_constructor' ]]);

        $container->register('code_transform.transformer.complete_constructor_public', function (Container $container) {
            return new CompleteConstructor(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class),
                'public',
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'complete_constructor_public' ]]);
        $container->register('code_transform.transformer.promote_constructor_private', function (Container $container) {
            return new CompleteConstructor(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class),
                'private',
                true,
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'promote_constructor' ]]);

        $container->register('code_transform.transformer.promote_constructor_public', function (Container $container) {
            return new CompleteConstructor(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class),
                'public',
                true,
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'promote_constructor_public' ]]);

        $container->register('code_transform.transformer.implement_contracts', function (Container $container) {
            return new ImplementContracts(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class),
                $container->get(BuilderFactory::class)
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'implement_contracts' ]]);

        $container->register('code_transform.transformer.fix_namespace_class_name', function (Container $container) {
            return new ClassNameFixerTransformer(
                $container->get('class_to_file.file_to_class')
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'fix_namespace_class_name' ]]);

        $container->register(UpdateDocblockReturnTransformer::class, function (Container $container) {
            return new UpdateDocblockReturnTransformer(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class),
                $container->get(BuilderFactory::class),
                $container->get(DocBlockUpdater::class),
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'add_missing_docblocks_return' ]]);

        $container->register(UpdateDocblockParamsTransformer::class, function (Container $container) {
            return new UpdateDocblockParamsTransformer(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class),
                $container->get(BuilderFactory::class),
                $container->get(DocBlockUpdater::class)
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'add_missing_params' ]]);
        $container->register(UpdateDocblockGenericTransformer::class, function (Container $container) {
            return new UpdateDocblockGenericTransformer(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class),
                $container->get(BuilderFactory::class),
                $container->get(DocBlockUpdater::class)
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'add_missing_class_generic' ]]);

        $container->register(UpdateReturnTypeTransformer::class, function (Container $container) {
            return new UpdateReturnTypeTransformer(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class),
                $container->get(BuilderFactory::class)
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'add_missing_return_types' ]]);

        $container->register('code_transform.transformer.add_missing_properties', function (Container $container) {
            return new AddMissingProperties(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(Updater::class)
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'add_missing_properties' ]]);

        $container->register('code_transform.transformer.remove_unused_imports', function (Container $container) {
            return new RemoveUnusedImportsTransformer(
                $container->expect(WorseReflectionExtension::SERVICE_REFLECTOR, Reflector::class),
                $container->get(WorseReflectionExtension::SERVICE_PARSER),
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'remove_unused_imports' ]]);
    }
}
