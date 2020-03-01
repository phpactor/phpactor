<?php

namespace Phpactor\Extension\CodeTransformExtra;

use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TolerantUpdater;
use Phpactor\CodeBuilder\Adapter\Twig\TwigExtension;
use Phpactor\CodeBuilder\Adapter\Twig\TwigRenderer;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;
use Phpactor\CodeBuilder\Domain\TemplatePathResolver\PhpVersionPathResolver;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeTransform\Adapter\Native\GenerateNew\ClassGenerator;
use Phpactor\CodeTransform\Adapter\TolerantParser\ClassToFile\Transformer\ClassNameFixerTransformer;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantChangeVisiblity;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantExtractExpression;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantImportClass;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantRenameVariable;
use Phpactor\CodeTransform\Adapter\WorseReflection\GenerateFromExisting\InterfaceFromExistingGenerator;
use Phpactor\CodeTransform\Adapter\WorseReflection\Helper\WorseUnresolvableClassNameFinder;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseExtractConstant;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseExtractMethod;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateAccessor;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseGenerateMethod;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseOverrideMethod;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\AddMissingProperties;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\CompleteConstructor;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\ImplementContracts;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\CodeTransformExtra\Rpc\ImportMissingClassesHandler;
use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Php\Model\PhpVersionResolver;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\CodeTransformExtra\Application\ClassInflect;
use Phpactor\Extension\CodeTransformExtra\Application\ClassNew;
use Phpactor\Extension\CodeTransformExtra\Application\Transformer;
use Phpactor\Extension\CodeTransformExtra\Command\ClassInflectCommand;
use Phpactor\Extension\CodeTransformExtra\Command\ClassNewCommand;
use Phpactor\Extension\CodeTransformExtra\Command\ClassTransformCommand;
use Phpactor\Extension\CodeTransformExtra\Rpc\ChangeVisiblityHandler;
use Phpactor\Extension\CodeTransformExtra\Rpc\ExtractConstantHandler;
use Phpactor\Extension\CodeTransformExtra\Rpc\ExtractExpressionHandler;
use Phpactor\Extension\CodeTransformExtra\Rpc\ExtractMethodHandler;
use Phpactor\Extension\CodeTransformExtra\Rpc\GenerateAccessorHandler;
use Phpactor\Extension\CodeTransformExtra\Rpc\GenerateMethodHandler;
use Phpactor\Extension\CodeTransformExtra\Rpc\ImportClassHandler;
use Phpactor\Extension\CodeTransformExtra\Rpc\OverrideMethodHandler;
use Phpactor\Extension\CodeTransformExtra\Rpc\RenameVariableHandler;
use Twig\Environment;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

class CodeTransformExtraExtension implements Extension
{
    const CLASS_NEW_VARIANTS = 'code_transform.class_new.variants';
    const TEMPLATE_PATHS = 'code_transform.template_paths';
    const INDENTATION = 'code_transform.indentation';
    const GENERATE_ACCESSOR_PREFIX = 'code_transform.refactor.generate_accessor.prefix';
    const GENERATE_ACCESSOR_UPPER_CASE_FIRST = 'code_transform.refactor.generate_accessor.upper_case_first';
    const APP_TEMPLATE_PATH = '%application_root%/vendor/phpactor/code-builder/templates';

    const PARAM_FIXER_INDENTATION = 'code_transform.fixer.indentation';
    const PARAM_FIXER_MEMBER_NEWLINES = 'code_transform.fixer.member_newlines';
    const SERVICE_TOLERANT_PARSER = 'code_transform.tolerant_parser';
    const PARAM_FIXER_TOLERANCE = 'code_transform.fixer.tolerance';
    const SERVICE_TEXT_FORMAT = 'code_transform.text_format';

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
        $schema->setDefaults([
            self::CLASS_NEW_VARIANTS => [],
            self::TEMPLATE_PATHS => [ // Ordered by priority
                '%project_config%/templates',
                '%config%/templates',
            ],
            self::INDENTATION => '    ',
            self::GENERATE_ACCESSOR_PREFIX => '',
            self::GENERATE_ACCESSOR_UPPER_CASE_FIRST => false,
            self::PARAM_FIXER_INDENTATION => true,
            self::PARAM_FIXER_MEMBER_NEWLINES => true,
            self::PARAM_FIXER_TOLERANCE => 80
        ]);
    }

    public function load(ContainerBuilder $container)
    {
        $this->registerConsole($container);
        $this->registerTransformers($container);
        $this->registerApplication($container);
        $this->registerRenderer($container);
        $this->registerUpdater($container);
        $this->registerRefactorings($container);
        $this->registerGenerators($container);
        $this->registerRpc($container);
    }

    private function registerApplication(ContainerBuilder $container)
    {
        $container->register('application.transform', function (Container $container) {
            return new Transformer(
                $container->get('code_transform.transform')
            );
        });

        $container->register('application.class_new', function (Container $container) {
            return new ClassNew(
                $container->get('application.helper.class_file_normalizer'),
                $container->get('code_transform.new_class_generators')
            );
        });

        $container->register('application.class_inflect', function (Container $container) {
            return new ClassInflect(
                $container->get('application.helper.class_file_normalizer'),
                $container->get('code_transform.from_existing_generators'),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        });
    }

    private function registerConsole(ContainerBuilder $container)
    {
        $container->register('command.transform', function (Container $container) {
            return new ClassTransformCommand(
                $container->get('application.transform')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'class:transform' ]]);

        $container->register('command.class_new', function (Container $container) {
            return new ClassNewCommand(
                $container->get('application.class_new'),
                $container->get('console.dumper_registry')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'class:new' ]]);

        $container->register('command.class_inflect', function (Container $container) {
            return new ClassInflectCommand(
                $container->get('application.class_inflect'),
                $container->get('console.dumper_registry')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'class:inflect' ]]);
    }

    private function registerTransformers(ContainerBuilder $container)
    {
        $container->register('code_transform.transformer.complete_constructor', function (Container $container) {
            return new CompleteConstructor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('code_transform.updater')
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'complete_constructor' ]]);

        $container->register('code_transform.transformer.implement_contracts', function (Container $container) {
            return new ImplementContracts(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('code_transform.updater'),
                $container->get('code_transform.builder_factory')
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'implement_contracts' ]]);

        $container->register('code_transform.transformer.fix_namespace_class_name', function (Container $container) {
            return new ClassNameFixerTransformer(
                $container->get('class_to_file.file_to_class')
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'fix_namespace_class_name' ]]);

        $container->register('code_transform.transformer.add_missing_properties', function (Container $container) {
            return new AddMissingProperties(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('code_transform.updater')
            );
        }, [ 'code_transform.transformer' => [ 'name' => 'add_missing_properties' ]]);
    }

    private function registerRenderer(ContainerBuilder $container)
    {
        $container->register('code_transform.twig_loader', function (Container $container) {
            $resolver = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER);
            $loader = new ChainLoader();
            $templatePaths = $container->getParameter(self::TEMPLATE_PATHS);
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
            ]);
            $renderer = new TwigRenderer($twig);
            $twig->addExtension(new TwigExtension($renderer, $container->get(self::SERVICE_TEXT_FORMAT)));

            return $renderer;
        });

        $container->register(self::SERVICE_TEXT_FORMAT, function (Container $container) {
            return new TextFormat($container->getParameter(self::INDENTATION));
        });
    }

    private function registerRefactorings(ContainerBuilder $container)
    {
        $container->register('code_transform.refactor.extract_constant', function (Container $container) {
            return new WorseExtractConstant(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('code_transform.updater')
            );
        });

        $container->register('code_transform.refactor.generate_method', function (Container $container) {
            return new WorseGenerateMethod(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('code_transform.builder_factory'),
                $container->get('code_transform.updater')
            );
        });

        $container->register('code_transform.refactor.generate_accessor', function (Container $container) {
            return new WorseGenerateAccessor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('code_transform.updater'),
                $container->getParameter(self::GENERATE_ACCESSOR_PREFIX),
                $container->getParameter(self::GENERATE_ACCESSOR_UPPER_CASE_FIRST)
            );
        });

        $container->register('code_transform.refactor.rename_variable', function (Container $container) {
            return new TolerantRenameVariable();
        });

        $container->register('code_transform.refactor.override_method', function (Container $container) {
            return new WorseOverrideMethod(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('code_transform.builder_factory'),
                $container->get('code_transform.updater')
            );
        });

        $container->register('code_transform.refactor.extract_method', function (Container $container) {
            return new WorseExtractMethod(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('code_transform.builder_factory'),
                $container->get('code_transform.updater')
            );
        });

        $container->register('code_transform.refactor.extract_expression', function (Container $container) {
            return new TolerantExtractExpression();
        });

        $container->register('code_transform.refactor.class_import', function (Container $container) {
            return new TolerantImportClass(
                $container->get('code_transform.updater')
            );
        });

        $container->register('code_transform.refactor.change_visiblity', function (Container $container) {
            return new TolerantChangeVisiblity();
        });

        $container->register('code_transform.helper.unresolvable_class_name_finder', function (Container $container) {
            return new WorseUnresolvableClassNameFinder(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        });
    }

    private function registerUpdater(ContainerBuilder $container)
    {
        $container->register('code_transform.updater', function (Container $container) {
            return new TolerantUpdater(
                $container->get('code_transform.renderer'),
                $container->get(self::SERVICE_TEXT_FORMAT),
                $container->get(self::SERVICE_TOLERANT_PARSER)
            );
        });
        $container->register('code_transform.builder_factory', function (Container $container) {
            return new WorseBuilderFactory($container->get(WorseReflectionExtension::SERVICE_REFLECTOR));
        });

        $container->register(self::SERVICE_TOLERANT_PARSER, function (Container $container) {
            return new Parser();
        });
    }

    private function registerRpc(ContainerBuilder $container)
    {
        $container->register('code_transform.rpc.handler.extract_constant', function (Container $container) {
            return new ExtractConstantHandler(
                $container->get('code_transform.refactor.extract_constant')
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ExtractConstantHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.extract_method', function (Container $container) {
            return new ExtractMethodHandler(
                $container->get('code_transform.refactor.extract_method')
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ExtractMethodHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.generate_accessor', function (Container $container) {
            return new GenerateAccessorHandler(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('code_transform.refactor.generate_accessor')
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => GenerateAccessorHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.generate_method', function (Container $container) {
            return new GenerateMethodHandler(
                $container->get('code_transform.refactor.generate_method')
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => GenerateMethodHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.refactor.import_class', function (Container $container) {
            return new ImportClassHandler(
                $container->get('code_transform.refactor.class_import'),
                $container->get('application.class_search'),
                SourceCodeFilesystemExtension::FILESYSTEM_COMPOSER
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ImportClassHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.rename_variable', function (Container $container) {
            return new RenameVariableHandler(
                $container->get('code_transform.refactor.rename_variable')
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => RenameVariableHandler::NAME] ]);

        $container->register('code_transform.handler.change_visiblity', function (Container $container) {
            return new ChangeVisiblityHandler(
                $container->get('code_transform.refactor.change_visiblity')
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ChangeVisiblityHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.override_method', function (Container $container) {
            return new OverrideMethodHandler(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('code_transform.refactor.override_method')
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => OverrideMethodHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.extract_expression', function (Container $container) {
            return new ExtractExpressionHandler(
                $container->get('code_transform.refactor.extract_expression')
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ExtractExpressionHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.import_unresolvable_classes', function (Container $container) {
            return new ImportMissingClassesHandler(
                $container->get(RpcExtension::SERVICE_REQUEST_HANDLER),
                $container->get('code_transform.helper.unresolvable_class_name_finder')
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ImportMissingClassesHandler::NAME] ]);
    }

    private function registerGenerators(ContainerBuilder $container)
    {
        $container->register('code_transform_extra.class_generator.variants', function (Container $container) {
            $generators = [
                'default' => new ClassGenerator($container->get('code_transform.renderer')),
            ];
            foreach ($container->getParameter(self::CLASS_NEW_VARIANTS) as $variantName => $variant) {
                $generators[$variantName] = new ClassGenerator($container->get('code_transform.renderer'), $variant);
            }

            return $generators;
        }, [ CodeTransformExtension::TAG_NEW_CLASS_GENERATOR => [] ]);

        $container->register('code_transform_extra.from_existing_generators', function (Container $container) {
            return new InterfaceFromExistingGenerator(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('code_transform.renderer')
            );

            return $generators;
        }, [ CodeTransformExtension::TAG_FROM_EXISTING_GENERATOR => [
            'name' => 'interface'
        ] ]);
    }
}
