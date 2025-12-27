<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Listener;

use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Event\Initialized;
use Psr\EventDispatcher\ListenerProviderInterface;

class StubValidationListener implements ListenerProviderInterface
{
    /**
     * @param list<string> $stubPaths
     */
    public function __construct(
        private readonly ClientApi $api,
        private readonly array $stubPaths
    ) {
    }

    /**
     * @return iterable<callable>
     */
    public function getListenersForEvent($event): iterable
    {
        if (!$event instanceof Initialized) {
            return;
        }


        yield function (): void {
            $invalidPaths = [];
            foreach ($this->stubPaths as $stubPath) {
                if (file_exists($stubPath) && is_file($stubPath)) {
                    continue;
                }

                $invalidPaths[] = $stubPath;
            }

            if ([] === $invalidPaths) {
                return;
            }

            $this->api->window()->showMessage()->warning(sprintf(
                'The following stubs could not be found or were not files: "%s"',
                implode('", "', $invalidPaths)
            ));
        };
    }
}
