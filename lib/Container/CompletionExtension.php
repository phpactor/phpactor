<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;
use Phpactor\Completion\Completor;
use Phpactor\Completion\Completor\ClassMemberCompletor;
use Phpactor\Completion\Completor\LocalVariableCompletor;

class CompletionExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(Container $container)
    {
        $container->register('completion.completor', function (Container $container) {

            $reflector = $container->get('reflection.reflector');
            $completors = [
                new ClassMemberCompletor($reflector),
                new LocalVariableCompletor($reflector),
            ];
            return new Completor($completors);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return [];
    }
}
