<?php

namespace Phpactor\Extension\ClassMover;

use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\Extension\ClassMover\Application\ClassCopy;
use Phpactor\Extension\ClassMover\Application\ClassMover as ClassMoverApp;
use Phpactor\Extension\ClassMover\Application\ClassReferences;
use Phpactor\ClassMover\Adapter\TolerantParser\TolerantClassReplacer;
use Phpactor\ClassMover\Adapter\WorseTolerant\WorseTolerantMemberFinder;
use Phpactor\ClassMover\Adapter\WorseTolerant\WorseTolerantMemberReplacer;
use Phpactor\ClassMover\ClassMover;
use Phpactor\Extension\ClassMover\Command\ClassCopyCommand;
use Phpactor\Extension\ClassMover\Command\ClassMoveCommand;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\ClassMover\Rpc\ClassCopyHandler;
use Phpactor\Extension\ClassMover\Rpc\ClassMoveHandler;
use Phpactor\Extension\ClassMover\Rpc\ReferencesHandler;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Navigation\NavigationExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\Container;
use Phpactor\Extension\ClassMover\Command\ReferencesMemberCommand;
use Phpactor\Extension\ClassMover\Command\ReferencesClassCommand;
use Phpactor\Extension\ClassMover\Application\ClassMemberReferences;

class ClassMoverExtension implements Extension
{
    public function configure(Resolver $schema): void
    {
    }


    public function load(ContainerBuilder $container): void
    {
        $this->registerClassMover($container);
        $this->registerApplicationServices($container);
        $this->registerConsoleCommands($container);
        $this->registerRpc($container);
    }

    private function registerRpc(ContainerBuilder $container): void
    {
        $container->register('class_mover.handler.class_references', function (Container $container) {
            return new ReferencesHandler(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('application.class_references'),
                $container->get('application.method_references'),
                $container->get(SourceCodeFilesystemExtension::SERVICE_REGISTRY)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ReferencesHandler::NAME] ]);

        $container->register('class_mover.handler.copy_class', function (Container $container) {
            return new ClassCopyHandler(
                $container->get('application.class_copy')
            );
        }, [ 'rpc.handler' => ['name' => ClassCopyHandler::NAME] ]);

        $container->register('class_mover.handler.move_class', function (Container $container) {
            return new ClassMoveHandler(
                $container->get(ClassMoverApp::class),
                SourceCodeFilesystemExtension::FILESYSTEM_GIT
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => [ 'name' => ClassMoveHandler::NAME ] ]);
    }

    private function registerClassMover(ContainerBuilder $container): void
    {
        $container->register('class_mover.member_finder', function (Container $container) {
            return new WorseTolerantMemberFinder(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        });

        $container->register('class_mover.member_replacer', function (Container $container) {
            return new WorseTolerantMemberReplacer();
        });

        $container->register('class_mover.ref_replacer', function (Container $container) {
            return new TolerantClassReplacer($container->get(Updater::class));
        });
    }

    private function registerApplicationServices(ContainerBuilder $container): void
    {
        $container->register(ClassMoverApp::class, function (Container $container) {
            return new ClassMoverApp(
                $container->get('application.helper.class_file_normalizer'),
                $container->get(ClassMover::class),
                $container->get('source_code_filesystem.registry'),
                $container->get(NavigationExtension::SERVICE_PATH_FINDER)
            );
        });

        $container->register('application.class_copy', function (Container $container) {
            return new ClassCopy(
                $container->get('application.helper.class_file_normalizer'),
                $container->get(ClassMover::class),
                $container->get('source_code_filesystem.registry')->get('git')
            );
        });

        $container->register('application.class_references', function (Container $container) {
            return new ClassReferences(
                $container->get('application.helper.class_file_normalizer'),
                $container->get('class_mover.class_finder'),
                $container->get('class_mover.ref_replacer'),
                $container->get('source_code_filesystem.registry')
            );
        });

        $container->register('application.method_references', function (Container $container) {
            return new ClassMemberReferences(
                $container->get('application.helper.class_file_normalizer'),
                $container->get('class_mover.member_finder'),
                $container->get('class_mover.member_replacer'),
                $container->get('source_code_filesystem.registry'),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        });
    }

    private function registerConsoleCommands(ContainerBuilder $container): void
    {
        $container->register('command.class_move', function (Container $container) {
            return new ClassMoveCommand(
                $container->get(ClassMoverApp::class),
                $container->get('console.prompter')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'class:move' ]]);

        $container->register('command.class_copy', function (Container $container) {
            return new ClassCopyCommand(
                $container->get('application.class_copy'),
                $container->get('console.prompter')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'class:copy' ]]);

        $container->register('command.class_references', function (Container $container) {
            return new ReferencesClassCommand(
                $container->get('application.class_references'),
                $container->get('console.dumper_registry')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'references:class' ]]);

        $container->register('command.member_references', function (Container $container) {
            return new ReferencesMemberCommand(
                $container->get('application.method_references'),
                $container->get('console.dumper_registry')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'references:member' ]]);
    }
}
