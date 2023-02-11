<?php

namespace Phpactor\Extension\LanguageServer\Service;

use Amp\Promise;
use Phpactor\Extension\Core\Application\Status;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use function Amp\call;

class OnDevelopWarningService implements ServiceProvider
{
    public function __construct(private ClientApi $client, private Status $status, private bool $warnOnDevelop)
    {
    }


    public function services(): array
    {
        if (false === $this->warnOnDevelop) {
            return [];
        }

        return [
            'serviceAnnouncements',
        ];
    }

    /**
     * @return Promise<void>
     */
    public function serviceAnnouncements(): Promise
    {
        return call(function (): void {
            $status = $this->status->check();

            if (false === $status['phpactor_is_develop']) {
                return;
            }

            $this->client->window()->showMessage()->warning(
                <<<'EOT'

                    Welcome to Phpactor!

                    You are using the develop branch which is no longer maintained
                    Switch to master or use the latest tagged version of Phpactor
                    EOT
            );
        });
    }
}
