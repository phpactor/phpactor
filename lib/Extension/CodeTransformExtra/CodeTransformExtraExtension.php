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
use Phpactor\CodeTransform\CodeTransform;
use Phpactor\CodeTransform\Domain\Helper\UnresolvableClassNameFinder;
use Phpactor\CodeTransform\Domain\Refactor\ExtractExpression;
use Phpactor\CodeTransform\Domain\Refactor\ChangeVisiblity;
use Phpactor\CodeTransform\Domain\Refactor\GenerateMethod;
use Phpactor\CodeTransform\Domain\Refactor\GenerateAccessor;
use Phpactor\CodeTransform\Domain\Refactor\ExtractMethod;
use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\CodeTransform\Domain\Refactor\OverrideMethod;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass;
use Phpactor\CodeTransform\Domain\Refactor\RenameVariable;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\CodeTransformExtra\Rpc\ClassNewHandler;
use Phpactor\Extension\CodeTransformExtra\Rpc\TransformHandler;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\CodeTransformExtra\Rpc\ClassInflectHandler;
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
        $this->registerApplication($container);
        $this->registerConsole($container);
        $this->registerRpc($container);
    }

    private function registerApplication(ContainerBuilder $container)
    {
        $container->register('application.transform', function (Container $container) {
            return new Transformer(
                $container->get(CodeTransform::class)
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

    private function registerRpc(ContainerBuilder $container)
    {
        $container->register('code_transform.rpc.handler.extract_constant', function (Container $container) {
            return new ExtractConstantHandler(
                $container->get(ExtractConstant::class)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ExtractConstantHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.extract_method', function (Container $container) {
            return new ExtractMethodHandler(
                $container->get(ExtractMethod::class)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ExtractMethodHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.generate_accessor', function (Container $container) {
            return new GenerateAccessorHandler(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(GenerateAccessor::class)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => GenerateAccessorHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.generate_method', function (Container $container) {
            return new GenerateMethodHandler(
                $container->get(GenerateMethod::class)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => GenerateMethodHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.refactor.import_class', function (Container $container) {
            return new ImportClassHandler(
                $container->get(ImportClass::class),
                $container->get('application.class_search'),
                SourceCodeFilesystemExtension::FILESYSTEM_COMPOSER
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ImportClassHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.rename_variable', function (Container $container) {
            return new RenameVariableHandler(
                $container->get(RenameVariable::class)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => RenameVariableHandler::NAME] ]);

        $container->register('code_transform.handler.change_visiblity', function (Container $container) {
            return new ChangeVisiblityHandler(
                $container->get(ChangeVisiblity::class)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ChangeVisiblityHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.override_method', function (Container $container) {
            return new OverrideMethodHandler(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(OverrideMethod::class)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => OverrideMethodHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.extract_expression', function (Container $container) {
            return new ExtractExpressionHandler(
                $container->get(ExtractExpression::class)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ExtractExpressionHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.import_unresolvable_classes', function (Container $container) {
            return new ImportMissingClassesHandler(
                $container->get(RpcExtension::SERVICE_REQUEST_HANDLER),
                $container->get(UnresolvableClassNameFinder::class)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ImportMissingClassesHandler::NAME] ]);
    }
}
