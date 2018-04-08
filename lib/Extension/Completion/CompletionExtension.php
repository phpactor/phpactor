<?php

namespace Phpactor\Extension\Completion;

use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Adapter\WorseReflection\Completor\WorseClassMemberCompletor;
use Phpactor\Completion\Adapter\WorseReflection\Completor\WorseLocalVariableCompletor;
use Phpactor\Extension\Extension;
use Phpactor\Extension\ContainerBuilder;
use Phpactor\Extension\Schema;
use Phpactor\Extension\Container;

class CompletionExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('completion.completor', function (Container $container) {
            $completors = [];
            foreach (array_keys($container->getServiceIdsForTag('completion.completor')) as $serviceId) {
                $completors[] = $container->get($serviceId);
            }
            return new Completor($completors);
        });

        $container->register('completion.completor.class_member', function (Container $container) {
            return new WorseClassMemberCompletor($container->get('reflection.reflector'));
        }, [ 'completion.completor' => []]);

        $container->register('completion.completor.local_variable', function (Container $container) {
            return new WorseLocalVariableCompletor($container->get('reflection.reflector'));
        }, [ 'completion.completor' => []]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Schema $schema)
    {
    }
}
