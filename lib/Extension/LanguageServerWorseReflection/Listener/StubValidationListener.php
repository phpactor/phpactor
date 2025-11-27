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
    public function __construct(private ClientApi $api, private array $stubPaths)
    {
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
                if (file_exists($stubPath)) {
                    continue;
                }

                $invalidPaths[] = $stubPath;
            }

            if ([] === $invalidPaths) {
                return;
            }

            $this->api->window()->showMessage()->warning(sprintf(
                'The following stubs could not be found: "%s"',
                implode('", "', $invalidPaths)
            ));
        };
    }
}
