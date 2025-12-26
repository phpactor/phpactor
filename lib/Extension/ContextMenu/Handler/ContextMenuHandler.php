<?php

namespace Phpactor\Extension\ContextMenu\Handler;

use Phpactor\CodeTransform\Domain\Helper\InterestingOffsetFinder;
use Phpactor\Extension\ContextMenu\Model\Action;
use Phpactor\Extension\ContextMenu\Model\ContextMenu;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Reflector;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Extension\ContextMenu\ContextMenuExtension;
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

    public function __construct(
        private readonly Reflector $reflector,
        private readonly InterestingOffsetFinder $offsetFinder,
        private readonly ClassFileNormalizer $classFileNormalizer,
        private readonly ContextMenu $menu,
        private readonly Container $container
    ) {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
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
        $offset = $this->offsetFromSourceAndOffset(
            $arguments[self::PARAMETER_SOURCE],
            $arguments[self::PARAMETER_OFFSET],
            $arguments[self::PARAMETER_CURRENT_PATH]
        );
        $symbol = $offset->nodeContext()->symbol();

        return $this->resolveAction($offset, $symbol, $arguments);
    }

    private function resolveAction(ReflectionOffset $offset, Symbol $symbol, array $arguments)
    {
        if (false === $this->menu->hasContext($symbol->symbolType())) {
            return EchoResponse::fromMessage(sprintf(
                'No context actions available for symbol type "%s"',
                $symbol->symbolType()
            ));
        }

        $symbolMenu = $this->menu->forContext($symbol->symbolType());

        if (null !== $arguments[self::PARAMETER_ACTION]) {
            return $this->delegateAction($symbolMenu, $arguments, $offset);
        }

        return $this->actionSelectionAction($symbol, $symbolMenu, $arguments);
    }

    private function delegateAction(array $symbolMenu, array $arguments, ReflectionOffset $offset): Response
    {
        $action = $symbolMenu[$arguments[self::PARAMETER_ACTION]];

        // to avoid a cyclic dependency we get the request handler from the container ...
        return $this->container->get(ContextMenuExtension::SERVICE_REQUEST_HANDLER)->handle(
            Request::fromNameAndParameters(
                $action->action(),
                $this->replaceTokens($action->parameters(), $offset, $arguments)
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
                    self::PARAMETER_OFFSET => $arguments[self::PARAMETER_OFFSET],
                    self::PARAMETER_CURRENT_PATH => $arguments[self::PARAMETER_CURRENT_PATH],
                ]
            ),
            [
                ChoiceInput::fromNameLabelChoices(
                    self::PARAMETER_ACTION,
                    sprintf('%s "%s":', ucfirst($symbol->symbolType()), $symbol->name()),
                    array_combine(array_keys($symbolMenu), array_keys($symbolMenu))
                )->withKeys(array_combine(array_keys($symbolMenu), array_map(function (Action $action) {
                    return $action->key();
                }, $symbolMenu)))
            ]
        );
    }

    private function offsetFromSourceAndOffset(string $source, int $offset, string $currentPath)
    {
        $sourceCode = TextDocumentBuilder::create($source)->uri($currentPath)->build();

        $interestingOffset = $this->offsetFinder->find(
            $sourceCode,
            ByteOffset::fromInt($offset)
        );

        return $this->reflector->reflectOffset(
            $sourceCode,
            $interestingOffset->toInt()
        );
    }

    private function replaceTokens(array $parameters, ReflectionOffset $offset, array $arguments)
    {
        $nodeContext = $offset->nodeContext();
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
                    $type = $nodeContext->containerType()->isDefined() ? $nodeContext->containerType() : $nodeContext->type();
                    $parameterValue = $this->classFileNormalizer->classToFile($type->generalize());
                    break;
                case '%offset%':
                    $parameterValue = $arguments[self::PARAMETER_OFFSET];
                    break;
                case '%source%':
                    $parameterValue = $arguments[self::PARAMETER_SOURCE];
                    break;
                case '%symbol%':
                    $parameterValue = $nodeContext->symbol()->name();
                    break;
            }

            $parameters[$parameterName] = $parameterValue;
        }

        return $parameters;
    }
}
