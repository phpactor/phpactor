<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Request;
use Phpactor\Rpc\ActionRequest;
use Phpactor\Rpc\RequestHandler;
use Phpactor\Rpc\Editor\ReturnChoiceAction;
use Phpactor\Rpc\Editor\ReturnOption;
use Phpactor\Rpc\Editor\InputCallbackAction;
use Phpactor\Rpc\Editor\Input\ChoiceInput;
use PhpBench\DependencyInjection\Container;
use Phpactor\Container\RpcExtension;
use Phpactor\WorseReflection\Core\Offset;

class ContextMenuHandler implements Handler
{
    const NAME = 'context_menu';

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var array
     */
    private $menu;

    /**
     * @var Container
     */
    private $container;

    public function __construct(Reflector $reflector, array $menu, Container $container)
    {
        $this->reflector = $reflector;
        $this->menu = $menu;
        $this->container = $container;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            'source' => null,
            'offset' => null,
            'action' => null,
        ];
    }

    public function handle(array $arguments)
    {
        $offset = $this->reflector->reflectOffset(SourceCode::fromString($arguments['source']), Offset::fromInt($arguments['offset']));
        $symbol = $offset->symbolInformation()->symbol();

        if (false === isset($this->menu[$symbol->symbolType()])) {
            return EchoAction::fromMessage(sprintf('No context actions available for symbol type "%s"', $symbol->symbolType()));
        }

        $symbolMenu = $this->menu[$symbol->symbolType()];

        if (null !== $arguments['action']) {
            $action = $symbolMenu[$arguments['action']];

            // to avoid a cyclic dependency we get the request handler from the container ...
            return $this->container->get(RpcExtension::SERVICE_REQUEST_HANDLER)->handle(
                Request::fromActions([
                    ActionRequest::fromNameAndParameters(
                        $action['action'],
                        $action['parameters']
                    )
                ])
            );
        }

        return InputCallbackAction::fromCallbackAndInputs(
            ActionRequest::fromNameAndParameters(
                self::NAME,
                [
                    'source' => $arguments['source'],
                    'offset' => (int) $arguments['offset'],
                ]
            ),
            [
                ChoiceInput::fromNameLabelChoices(
                    'action',
                    sprintf('Context action on "%s"', $symbol->name()),
                    array_combine(array_keys($symbolMenu), array_keys($symbolMenu))
                )
            ]
        );
    }
}

