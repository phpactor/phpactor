<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Adapter\WorseReflection\Completor\WorseClassMemberCompletor;
use Phpactor\Completion\Adapter\WorseReflection\Completor\WorseLocalVariableCompletor;

class CompletionExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(Container $container)
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
    public function getDefaultConfig()
    {
        return [];
    }
}
