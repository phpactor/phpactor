<?php

namespace Phpactor\Extension\CodeTransformExtra;

use Phpactor\CodeTransform\CodeTransform;
use Phpactor\CodeTransform\Domain\Refactor\ExtractExpression;
use Phpactor\CodeTransform\Domain\Refactor\ChangeVisiblity;
use Phpactor\CodeTransform\Domain\Refactor\GenerateMember;
use Phpactor\CodeTransform\Domain\Refactor\ExtractMethod;
use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\CodeTransform\Domain\Refactor\OverrideMethod;
use Phpactor\CodeTransform\Domain\Refactor\ImportName;
use Phpactor\CodeTransform\Domain\Refactor\RenameVariable;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\CodeTransformExtra\Rpc\ImportMissingClassesHandler;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
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
use Phpactor\Extension\CodeTransformExtra\Rpc\PropertyAccessGeneratorHandler;
use Phpactor\Extension\CodeTransformExtra\Rpc\GenerateMethodHandler;
use Phpactor\Extension\CodeTransformExtra\Rpc\ImportClassHandler;
use Phpactor\Extension\CodeTransformExtra\Rpc\OverrideMethodHandler;
use Phpactor\Extension\CodeTransformExtra\Rpc\RenameVariableHandler;

class CodeTransformExtraExtension implements Extension
{
    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
        ]);
    }

    public function load(ContainerBuilder $container): void
    {
        $this->registerApplication($container);
        $this->registerConsole($container);
        $this->registerRpc($container);
    }

    private function registerApplication(ContainerBuilder $container): void
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
                LoggingExtension::channelLogger($container, 'CT'),
            );
        });
    }

    private function registerConsole(ContainerBuilder $container): void
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

    private function registerRpc(ContainerBuilder $container): void
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
            return new PropertyAccessGeneratorHandler(
                'generate_accessor',
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('code_transform.generate_accessor')
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => 'generate_accessor'] ]);

        $container->register('code_transform.rpc.handler.generate_mutator', function (Container $container) {
            return new PropertyAccessGeneratorHandler(
                'generate_mutator',
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('code_transform.generate_mutator')
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => 'generate_mutator'] ]);

        $container->register('code_transform.rpc.handler.generate_method', function (Container $container) {
            return new GenerateMethodHandler(
                $container->get(GenerateMember::class)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => GenerateMethodHandler::NAME] ]);

        $container->register('code_transform.rpc.handler.refactor.import_class', function (Container $container) {
            return new ImportClassHandler(
                $container->get(ImportName::class),
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
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ImportMissingClassesHandler::NAME] ]);
    }
}
