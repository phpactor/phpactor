<?php

namespace Phpactor\Extension\Completion;

use Phpactor\Completion\Adapter\WorseReflection\Formatter\MethodFormatter;
use Phpactor\Completion\Adapter\WorseReflection\Formatter\VariableFormatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Completion\Adapter\WorseReflection\Formatter\ParameterFormatter;
use Phpactor\Completion\Adapter\WorseReflection\Formatter\PropertyFormatter;
use Phpactor\Completion\Adapter\WorseReflection\Formatter\TypeFormatter;
use Phpactor\Completion\Adapter\WorseReflection\Formatter\TypesFormatter;
use Phpactor\Completion\Adapter\WorseReflection\Formatter\VariableWithNodeFormatter;
use Phpactor\Completion\Core\ChainCompletor;
use Phpactor\Completion\Core\Completor;
use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Schema;
use Phpactor\Container\Container;
use Phpactor\Extension\Completion\Command\CompleteCommand;
use Phpactor\Extension\Completion\Application\Complete;
use Phpactor\Completion\Adapter\WorseReflection\Completor\WorseClassMemberCompletor;
use Phpactor\Completion\Adapter\WorseReflection\Completor\WorseLocalVariableCompletor;

class CompletionExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $this->registerCompletion($container);
        $this->registerCommands($container);
        $this->registerApplicationServices($container);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Schema $schema)
    {
    }

    private function registerCompletion(ContainerBuilder $container)
    {
        $container->register('completion.completor', function (Container $container) {
            $completors = [];
            foreach (array_keys($container->getServiceIdsForTag('completion.completor')) as $serviceId) {
                $completors[] = $container->get($serviceId);
            }
            return new ChainCompletor($completors);
        });
        
        $container->register('completion.completor.class_member', function (Container $container) {
            return new WorseClassMemberCompletor(
                $container->get('reflection.reflector'),
                $container->get('reflection.tolerant_parser'),
                $container->get('completion.formatter')
            );
        }, [ 'completion.completor' => []]);
        
        $container->register('completion.completor.local_variable', function (Container $container) {
            return new WorseLocalVariableCompletor(
                $container->get('reflection.reflector'),
                $container->get('reflection.tolerant_parser'),
                $container->get('completion.formatter')
            );
        }, [ 'completion.completor' => []]);

        $container->register('completion.formatter', function (Container $container) {
            return new ObjectFormatter([
                new TypeFormatter(),
                new TypesFormatter(),
                new MethodFormatter(),
                new ParameterFormatter(),
                new PropertyFormatter(),
                new VariableFormatter(),
            ]);
        });
    }

    private function registerCommands(ContainerBuilder $container)
    {
        $container->register('command.complete', function (Container $container) {
            return new CompleteCommand(
                $container->get('application.complete'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => []]);
    }

    private function registerApplicationServices(ContainerBuilder $container)
    {
        $container->register('application.complete', function (Container $container) {
            return new Complete(
                $container->get('completion.completor'),
                $container->get('application.helper.class_file_normalizer')
            );
        });
    }
}
