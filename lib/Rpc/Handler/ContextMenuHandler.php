<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Request;
use Phpactor\Rpc\Editor\InputCallbackAction;
use Phpactor\Rpc\Editor\Input\ChoiceInput;
use PhpBench\DependencyInjection\Container;
use Phpactor\Container\RpcExtension;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\Rpc\Editor\StackAction;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\Rpc\Response;

class ContextMenuHandler implements Handler
{
    const NAME = 'context_menu';
    const PARAMETER_SOURCE = 'source';
    const PARAMETER_OFFSET = 'offset';
    const PARAMETER_ACTION = 'action';
    const PARAMETER_CURRENT_PATH = 'current_path';

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
            self::PARAMETER_CURRENT_PATH => null,
        ];
    }

    public function handle(array $arguments)
    {
        $offset = $this->offsetFromSourceAndOffset($arguments[self::PARAMETER_SOURCE], $arguments[self::PARAMETER_OFFSET]);
        $symbol = $offset->symbolInformation()->symbol();

        return $this->resolveAction($offset, $symbol, $arguments);
    }

    private function resolveAction(ReflectionOffset $offset, Symbol $symbol, array $arguments)
    {
        if (false === isset($this->menu[$symbol->symbolType()])) {
            return EchoAction::fromMessage(sprintf(
                'No context actions available for symbol type "%s"',
                $symbol->symbolType()
            ));
        }

        $symbolMenu = $this->menu[$symbol->symbolType()];

        if (null !== $arguments[self::PARAMETER_ACTION]) {
            return $this->delegateAction($symbolMenu, $arguments, $offset);
        }

        return $this->actionSelectionAction($symbol, $symbolMenu, $arguments);
    }

    private function delegateAction(array $symbolMenu, array $arguments, ReflectionOffset $offset): Response
    {
        $action = $symbolMenu[$arguments[self::PARAMETER_ACTION]];

        // to avoid a cyclic dependency we get the request handler from the container ...
        return $this->container->get(RpcExtension::SERVICE_REQUEST_HANDLER)->handle(
            Request::fromNameAndParameters(
                $action[self::PARAMETER_ACTION],
                $this->replaceTokens($action['parameters'], $offset, $arguments)
            )
        );
    }

    private function actionSelectionAction(Symbol $symbol, $symbolMenu, array $arguments): InputCallbackAction
    {
        return InputCallbackAction::fromCallbackAndInputs(
            Request::fromNameAndParameters(
                self::NAME,
                [
                    self::PARAMETER_SOURCE => $arguments[self::PARAMETER_SOURCE],
                    self::PARAMETER_OFFSET => (int) $arguments[self::PARAMETER_OFFSET],
                    self::PARAMETER_CURRENT_PATH => $arguments[self::PARAMETER_CURRENT_PATH],
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

    private function offsetFromSourceAndOffset(string $source, int $offset)
    {
        return $this->reflector->reflectOffset(
            SourceCode::fromString($source),
            Offset::fromInt($offset)
        );
    }

    private function replaceTokens(array $parameters, ReflectionOffset $offset, array $arguments)
    {
        $symbolInformation = $offset->symbolInformation();
        foreach ($parameters as $parameterName => $parameterValue) {
            switch ($parameterValue) {
                case '%current_path%':
                    $parameterValue = $arguments[self::PARAMETER_CURRENT_PATH];
                    break;
                case '%path%':
                    // TODO: the "path" of the reflected type. You might expect
                    // this to be the current path but it is not. It is used
                    // when we want to act on the file in the "type" under the
                    // cursor. this shouldn't be a thing.
                    $type = $symbolInformation->hasContainerType() ? $symbolInformation->containerType() : $symbolInformation->type();
                    $parameterValue = $this->classFileNormalizer->classToFile($type);
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
