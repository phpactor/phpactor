<?php

namespace Phpactor\Extension\LanguageServerBlackfire\Handler;

use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServerBlackfire\BlackfireProfiler;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\ClientApi;

class BlackfireHandler implements Handler
{
    public function __construct(
        private BlackfireProfiler $profiler,
        private ClientApi $client
    ) {
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
        $this->client->window()->showMessage()->info(
            'Blackfire profiling started',
        );
        $this->profiler->start();

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
        $url = $this->profiler->done();
        $this->client->window()->showMessage()->info(sprintf(
            'Blackfire profile created: %s',
            $url
        ));
        return new Success(null);
    }
}
