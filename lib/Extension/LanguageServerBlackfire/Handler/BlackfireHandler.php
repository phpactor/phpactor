<?php

namespace Phpactor\Extension\LanguageServerBlackfire\Handler;

use Amp\Promise;
use Amp\Success;
use Blackfire\Client;
use Blackfire\Probe;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use RuntimeException;

class BlackfireHandler implements Handler
{
    private Client $blackfire;
    private ClientApi $client;

    private ?Probe $probe = null;

    public function __construct(Client $blackfire, ClientApi $client)
    {
        $this->blackfire = $blackfire;
        $this->client = $client;
    }
    public function methods(): array
    {
        return [
            'blackfire/start' => 'start',
            'blackfire/finish' => 'finish',
        ];
    }

    /**
     * @return Promise<null>
     */
    public function start(): Promise
    {
        if ($this->probe) {
            throw new RuntimeException(sprintf('Probe already started'));
        }
        $this->client->window()->showMessage()->info(
            'Blackfire probe enabled',
        );

        $this->probe = $this->blackfire->createProbe();
        return new Success(null);
    }

    /**
     * @return Promise<null>
     */
    public function finish(): Promise
    {
        $this->client->window()->showMessage()->info(
            'Blackfire profile creating....',
        );
        $profile = $this->blackfire->endProbe($this->probe);
        $this->client->window()->showMessage()->info(sprintf(
            'Blackfire profile created: %s',
            $profile->getUrl()
        ));
        $this->probe = null;
        return new Success(null);
    }

}
