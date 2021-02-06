<?php

namespace Phpactor\Extension\LanguageServer\Service;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use function Amp\call;
use function Amp\delay;

class OnDevelopWarningService implements ServiceProvider
{
    /**
     * @var ClientApi
     */
    private $client;

    /**
     * @var bool
     */
    private $warnOnDevelop;

    public function __construct(ClientApi $client, bool $warnOnDevelop)
    {
        $this->client = $client;
        $this->warnOnDevelop = $warnOnDevelop;
    }

    /**
     * {@inheritDoc}
     */
    public function services(): array
    {
        if (false === $this->warnOnDevelop) {
            return [];
        }

        return [
            'serviceAnnouncements',
        ];
    }

    public function serviceAnnouncements(): Promise
    {
        return call(function () {
            $this->client->window()->showMessage()->warning(<<<'EOT'

            Welcome to Phpactor!

            You are using the develop branch which is no longer maintained
            Switch to master or use the latest tagged version of Phpactor
            EOT
            );
        });
    }
}
