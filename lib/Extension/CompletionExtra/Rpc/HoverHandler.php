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

    private Reflector $reflector;

    private ObjectFormatter $formatter;

    public function __construct(Reflector $reflector, ObjectFormatter $formatter)
    {
        $this->reflector = $reflector;
        $this->formatter = $formatter;
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
        switch ($symbolContext->symbol()->symbolType()) {
            case Symbol::METHOD:
            case Symbol::PROPERTY:
            case Symbol::CONSTANT:
                return $this->renderMember($symbolContext);
            case Symbol::CLASS_:
                return $this->renderClass($symbolContext->type());
            case Symbol::FUNCTION:
                return $this->renderFunction($symbolContext);
            case Symbol::VARIABLE:
                return $this->renderVariable($symbolContext);
        }

        return null;
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
            switch ($symbolContext->symbol()->symbolType()) {
                case Symbol::METHOD:
                    $member = $class->methods()->get($name);
                    break;
                case Symbol::CONSTANT:
                    $member = $class->members()->get($name);
                    break;
                case Symbol::PROPERTY:
                    $member = $class->members()->get($name);
                    break;
                default:
                    throw new RuntimeException('Unknown member type');
            }


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
        } catch (CouldNotFormat $e) {
        }

        return null;
    }
}
