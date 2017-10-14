<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Request;
use Phpactor\Rpc\ActionRequest;
use Phpactor\Rpc\Editor\InputCallbackAction;
use Phpactor\Rpc\Editor\Input\ChoiceInput;
use PhpBench\DependencyInjection\Container;
use Phpactor\Container\RpcExtension;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\Rpc\Editor\StackAction;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\Phpactor;

class ContextMenuHandler implements Handler
{
    const NAME = 'context_menu';
    const PARAMETER_SOURCE = 'source';
    const PARAMETER_OFFSET = 'offset';
    const PARAMETER_ACTION = 'action';

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

    /**
     * @var ClassFileNormalizer
     */
    private $classFileNormalizer;

    public function __construct(
        Reflector $reflector,
        ClassFileNormalizer $classFileNormalizer,
        array $menu,
        Container $container
    ) {
        $this->reflector = $reflector;
        $this->menu = $menu;
        $this->container = $container;
        $this->classFileNormalizer = $classFileNormalizer;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            self::PARAMETER_SOURCE => null,
            self::PARAMETER_OFFSET => null,
            self::PARAMETER_ACTION => null,
        ];
    }

    public function handle(array $arguments)
    {
        $offset = $this->reflector->reflectOffset(
            SourceCode::fromString($arguments[self::PARAMETER_SOURCE]),
            Offset::fromInt($arguments[self::PARAMETER_OFFSET])
        );

        $symbol = $offset->symbolInformation()->symbol();

        if (false === isset($this->menu[$symbol->symbolType()])) {
            return EchoAction::fromMessage(sprintf('No context actions available for symbol type "%s"', $symbol->symbolType()));
        }

        $symbolMenu = $this->menu[$symbol->symbolType()];

        if (null !== $arguments[self::PARAMETER_ACTION]) {
            $action = $symbolMenu[$arguments[self::PARAMETER_ACTION]];

            // to avoid a cyclic dependency we get the request handler from the container ...
            $response = $this->container->get(RpcExtension::SERVICE_REQUEST_HANDLER)->handle(
                Request::fromActions([
                    ActionRequest::fromNameAndParameters(
                        $action[self::PARAMETER_ACTION],
                        $this->replaceTokens($action['parameters'], $offset, $arguments)
                    )
                ])
            );

            return StackAction::fromActions($response->actions());
        }

        return InputCallbackAction::fromCallbackAndInputs(
            ActionRequest::fromNameAndParameters(
                self::NAME,
                [
                    self::PARAMETER_SOURCE => $arguments[self::PARAMETER_SOURCE],
                    self::PARAMETER_OFFSET => (int) $arguments[self::PARAMETER_OFFSET],
                ]
            ),
            [
                ChoiceInput::fromNameLabelChoices(
                    self::PARAMETER_ACTION,
                    sprintf('%s "%s":', ucfirst($symbol->symbolType()), $symbol->name()),
                    array_combine(array_keys($symbolMenu), array_keys($symbolMenu))
                )
            ]
        );
    }

    private function replaceTokens(array $parameters, ReflectionOffset $offset, array $arguments)
    {
        $symbolInformation = $offset->symbolInformation();
        foreach ($parameters as $parameterName => $parameterValue) {
            switch ($parameterValue) {
                case '%path%':
                    $type = $symbolInformation->hasContainerType() ? $symbolInformation->containerType() : $symbolInformation->type();
                    $path = $this->classFileNormalizer->classToFile($type);
                    $parameterValue = Phpactor::relativizePath($path);
                    break;
                case '%offset%':
                    $parameterValue = $arguments[self::PARAMETER_OFFSET];
                    break;
                case '%source%':
                    $parameterValue = $arguments[self::PARAMETER_SOURCE];
                    break;
            }

            $parameters[$parameterName] = $parameterValue;
        }

        return $parameters;
    }
}
