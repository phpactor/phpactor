<?php

namespace Phpactor\Extension\LanguageServer\Listener;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Event\Initialized;
use Phpactor\MapResolver\InvalidMap;
use Phpactor\MapResolver\ResolverErrors;
use Phpactor\MapResolver\UnknownKeys;
use Psr\EventDispatcher\ListenerProviderInterface;

class InvalidConfigListener implements ListenerProviderInterface
{
    public function __construct(
        private readonly ClientApi $clientApi,
        private readonly ResolverErrors $errors
    ) {
    }

    /**
     * @return iterable<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof Initialized) {
            return [[$this, 'handleInvalidConfig']];
        }

        return [];
    }

    /**
     * @return Success<null>
     */
    public function handleInvalidConfig(): Promise
    {
        if ($this->errors->errors()) {
            $this->clientApi->window()->showMessage()->warning(sprintf(
                'Phpactor configuration error: %s',
                implode(', ', array_map(function (InvalidMap $error) {
                    if ($error instanceof UnknownKeys) {
                        $suggestions = $this->suggestions($error);
                        if (count($suggestions)) {
                            return sprintf(
                                'Unknown configuration keys: "%s", did you mean any of: "%s"',
                                implode('", "', $error->additionalKeys()),
                                implode('", "', $suggestions),
                            );
                        }

                        return sprintf(
                            'Unknown configuration keys: "%s"',
                            implode('", "', $error->additionalKeys()),
                        );
                    }
                    return $error->getMessage();
                }, $this->errors->errors()))
            ));
        }
        return new Success();
    }

    /**
     * @return list<string>
     */
    private function suggestions(UnknownKeys $error): array
    {
        $suggestions = array_filter($error->allowedKeys(), function (string $allowed) use ($error) {
            foreach ($error->additionalKeys() as $key) {
                if (levenshtein($key, $allowed) < 10) {
                    return true;
                }
            }

            return false;
        });

        return array_values($suggestions);
    }
}
