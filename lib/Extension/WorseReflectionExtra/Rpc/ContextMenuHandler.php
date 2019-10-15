<?php

namespace Phpactor\Extension\Rpc\Handler;

use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\Extension\Core\Application\Helper\ClassFileNormalizer;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\Extension\Rpc\Response;
use Phpactor\Container\Container;

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

    public function configure(Resolver $resolver)
    {
        $resolver->setRequired([
            self::PARAMETER_SOURCE,
            self::PARAMETER_OFFSET,
        ]);

        $resolver->setDefaults([
            self::PARAMETER_ACTION => null,
            self::PARAMETER_CURRENT_PATH => null,
        ]);
    }

    public function handle(array $arguments)
    {
        $offset = $this->offsetFromSourceAndOffset($arguments[self::PARAMETER_SOURCE], $arguments[self::PARAMETER_OFFSET], $arguments[self::PARAMETER_CURRENT_PATH]);
        $symbol = $offset->symbolContext()->symbol();

        return $this->resolveAction($offset, $symbol, $arguments);
    }

    private function resolveAction(ReflectionOffset $offset, Symbol $symbol, array $arguments)
    {
        if (false === isset($this->menu[$symbol->symbolType()])) {
            return EchoResponse::fromMessage(sprintf(
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

    private function actionSelectionAction(Symbol $symbol, $symbolMenu, array $arguments): InputCallbackResponse
    {
        return InputCallbackResponse::fromCallbackAndInputs(
            Request::fromNameAndParameters(
                self::NAME,
                [
                    self::PARAMETER_SOURCE => $arguments[self::PARAMETER_SOURCE],
                    self::PARAMETER_OFFSET => $symbol->position()->start(),
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

    private function offsetFromSourceAndOffset(string $source, int $offset, string $currentPath)
    {
        return $this->reflector->reflectOffset(
            SourceCode::fromPathAndString($currentPath, $source),
            Offset::fromInt($offset),
            true
        );
    }

    private function replaceTokens(array $parameters, ReflectionOffset $offset, array $arguments)
    {
        $symbolContext = $offset->symbolContext();
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
                    $type = $symbolContext->hasContainerType() ? $symbolContext->containerType() : $symbolContext->type();
                    $parameterValue = $this->classFileNormalizer->classToFile($type);
                    break;
                case '%offset%':
                    $parameterValue = $arguments[self::PARAMETER_OFFSET];
                    break;
                case '%source%':
                    $parameterValue = $arguments[self::PARAMETER_SOURCE];
                    break;
                case '%symbol%':
                    $parameterValue = $symbolContext->symbol()->name();
                    break;
            }

            $parameters[$parameterName] = $parameterValue;
        }

        return $parameters;
    }
}
