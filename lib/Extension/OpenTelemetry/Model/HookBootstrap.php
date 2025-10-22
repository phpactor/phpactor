<?php

namespace Phpactor\Extension\OpenTelemetry\Model;

use Composer\InstalledVersions;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKeys;
use RuntimeException;
use Throwable;
use function OpenTelemetry\Instrumentation\hook;

final class HookBootstrap
{
    public bool $initialized = false;

    /**
     * @param HookProvider[] $providers
     */
    public function __construct(private array $providers)
    {
    }

    public function bootstrap(): void
    {
        if (!extension_loaded('opentelemetry')) {
            return;
        }

        $instrumentation = new CachedInstrumentation(
            name: 'Phpactor',
            version: InstalledVersions::getVersion('phpactor/phpactor'),
        );

        $tracer = $instrumentation->tracer();

        foreach ($this->providers as $provider) {
            foreach ($provider->hooks() as $hook) {
                hook($hook->class, $hook->function, function (
                    object $object,
                    array $params,
                    string $class,
                    string $function,
                    ?string $filename,
                    ?int $lineno,
                ) use ($tracer, $hook): void {
                    $callContext = new PreContext(
                        $object,
                        $params,
                        $class,
                        $function,
                        $filename,
                        $lineno,
                    );
                    $tracerContext = new TracerContext($tracer, Context::getCurrent(), Context::storage());
                    $span = ($hook->pre)($tracerContext, $callContext);
                    Context::storage()->attach($span->storeInContext($tracerContext->currentContext()));
                }, function (object $object, array $params, mixed $returnValue, ?Throwable $exception) use ($tracer, $hook) {
                    $tracerContext = new TracerContext($tracer, Context::getCurrent(), Context::storage());
                    $postContext = new PostContext($object, $params, $returnValue, $exception);
                    if ($hook->post !== null) {
                        return ($hook->post)($tracerContext, $postContext);
                    }
                    $scope = Context::storage()->scope();
                    if (null === $scope) {
                        throw new RuntimeException(
                            'Expected scope from context storage, but got NULL'
                        );
                    }
                    $scope->detach();
                    $span = $scope->context()->get(ContextKeys::span());
                    if (!$span instanceof SpanInterface) {
                        throw new RuntimeException(sprintf(
                            'Expected Span from context , but got %s',
                            get_debug_type($span)
                        ));
                    }
                    $span->end();
                    return $returnValue;
                });
            }
        }

        $this->initialized = true;
    }
}
