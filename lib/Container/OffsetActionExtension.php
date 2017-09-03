<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;
use Phpactor\OffsetAction\ActionRegistry;
use Phpactor\OffsetAction\Action\GotoDefinitionAction;
use Phpactor\WorseReflection\Core\Reflection\Inference\Symbol;

class OffsetActionExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(Container $container)
    {
        $this->registerActions($container);
    }

    private function registerActions(Container $container)
    {
        $container->register('offset_action.action_registry', function (Container $container) {
            $actions = [];
            foreach ($container->getServiceIdsForTag('offset_action.action') as $serviceId => $attrs) {
                $symbolTypes = $attrs['types'];
                $name = $attrs['name'];

                foreach ($symbolTypes as $symbolType) {
                    if (!isset($actions[$symbolType])) {
                        $actions[$symbolType] = [];
                    }

                    $actions[$symbolType][$name] = $container->get($serviceId);
                }

                return new ActionRegistry($actions);
            }
        });

        $container->register('offset_action.action.goto_definition', function (Container $container) {
            return new GotoDefinitionAction($container->get('reflection.reflector'));
        }, [
            'offset_action.action' => [
                'name' => 'goto_definition',
                'types' => [ Symbol::METHOD, Symbol::PROPERTY, Symbol::CONSTANT ]
            ]
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return [];
    }
}

