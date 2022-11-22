<?php

namespace Phpactor\Extension\CompletionExtra\Rpc;

use Phpactor\Completion\Core\Exception\CouldNotFormat;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\MapResolver\Resolver;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Reflector;
use RuntimeException;

class HoverHandler implements Handler
{
    const PARAM_SOURCE = 'source';
    const PARAM_OFFSET = 'offset';
    const NAME = 'hover';

    public function __construct(private Reflector $reflector, private ObjectFormatter $formatter)
    {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setRequired([
            self::PARAM_SOURCE,
            self::PARAM_OFFSET,
        ]);
    }

    /**
     * @param array<string,mixed> $arguments
     */
    public function handle(array $arguments): Response
    {
        $offset = $this->reflector->reflectOffset($arguments[self::PARAM_SOURCE], $arguments[self::PARAM_OFFSET]);

        $type = $offset->symbolContext()->type();
        $symbolContext = $offset->symbolContext();

        $info = $this->messageFromSymbolContext($symbolContext);
        $info = $info ?: sprintf(
            '%s %s',
            $symbolContext->symbol()->symbolType(),
            $symbolContext->symbol()->name()
        );

        return EchoResponse::fromMessage($info);
    }

    private function renderSymbolContext(NodeContext $symbolContext): ?string
    {
        return match ($symbolContext->symbol()->symbolType()) {
            Symbol::METHOD, Symbol::PROPERTY, Symbol::CONSTANT => $this->renderMember($symbolContext),
            Symbol::CLASS_ => $this->renderClass($symbolContext->type()),
            Symbol::FUNCTION => $this->renderFunction($symbolContext),
            Symbol::VARIABLE => $this->renderVariable($symbolContext),
            default => null,
        };
    }

    private function renderMember(NodeContext $symbolContext): ?string
    {
        $name = $symbolContext->symbol()->name();
        $container = $symbolContext->containerType();

        try {
            $class = $this->reflector->reflectClassLike((string) $container);
            $member = null;

            // note that all class-likes (classes, traits and interfaces) have
            // methods but not all have constants or properties, so we play safe
            // with members() which is first-come-first-serve, rather than risk
            // a fatal error because of a non-existing method.
            $member = match ($symbolContext->symbol()->symbolType()) {
                Symbol::METHOD => $class->methods()->get($name),
                Symbol::CONSTANT => $class->members()->get($name),
                Symbol::PROPERTY => $class->members()->get($name),
                default => throw new RuntimeException('Unknown member type'),
            };


            return $this->formatter->format($member);
        } catch (NotFound $e) {
            return $e->getMessage();
        }
    }

    private function renderFunction(NodeContext $symbolContext)
    {
        $name = $symbolContext->symbol()->name();
        $function = $this->reflector->reflectFunction($name);

        return $this->formatter->format($function);
    }

    private function renderVariable(NodeContext $symbolContext)
    {
        return $this->formatter->format($symbolContext->type());
    }

    private function renderClass(Type $type)
    {
        try {
            $class = $this->reflector->reflectClassLike((string) $type);
            return $this->formatter->format($class);
        } catch (NotFound $e) {
            return $e->getMessage();
        }
    }

    private function messageFromSymbolContext(NodeContext $symbolContext): ?string
    {
        try {
            return $this->renderSymbolContext($symbolContext);
        } catch (CouldNotFormat) {
        }

        return null;
    }
}
