<?php

namespace Phpactor\Extension\OpenTelemetry\Model;

use Composer\InstalledVersions;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
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
                ) use ($tracer, $hook) {
                    $callContext = new PreContext(
                        $object,
                        $params,
                        $class,
                        $function,
                        $filename,
                        $lineno,
                    );
                    ($hook->pre)($tracer, $callContext);
                }, function (object $object, array $params, mixed $returnValue, ?Throwable $exception) use ($tracer, $hook) {
                    $postContext = new PostContext(
                        $object,
                        $params,
                        $returnValue,
                        $exception
                    );
                    ($hook->post)($tracer, $postContext);
                });
            }
        }

        $this->initialized = true;
    }
}
