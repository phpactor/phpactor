<?php

namespace Phpactor\Extension\LanguageServer\Formatter;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Formatting\Formatter;
use Phpactor\LanguageServer\Core\Server\ClientApi;

class NoFormatterConfiguredFormatter implements Formatter
{
    private ClientApi $client;

    public function __construct(ClientApi $client)
    {
        $this->client = $client;
    }


    public function format(TextDocumentItem $textDocument): Promise
    {
        $this->client->window()->showMessage()->error(sprintf(
            'No formatter has been enabled, please check your configuration'
        ));
        return new Success([]);
    }
}
