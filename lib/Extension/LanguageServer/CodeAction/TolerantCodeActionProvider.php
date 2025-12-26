<?php

namespace Phpactor\Extension\LanguageServer\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use function Amp\call;
use Throwable;

final class TolerantCodeActionProvider implements CodeActionProvider
{
    public function __construct(
        private readonly CodeActionProvider $provider,
        private readonly ClientApi $client
    ) {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $range, $cancel) {
            try {
                return yield $this->provider->provideActionsFor($textDocument, $range, $cancel);
            } catch (Throwable $error) {
                $this->client->window()->showMessage()->error(sprintf(
                    'Provider %s (%s) failed: %s',
                    $this->provider::class,
                    $this->provider->describe(),
                    $error->getMessage(),
                ));
                return [];
            }
        });
    }

    public function kinds(): array
    {
        return $this->provider->kinds();
    }

    public function describe(): string
    {
        return $this->provider->describe();
    }
}
