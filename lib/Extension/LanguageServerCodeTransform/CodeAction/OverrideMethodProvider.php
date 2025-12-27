<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\OverrideMethodCommand;
use Phpactor\Extension\LanguageServerCodeTransform\Model\OverrideMethod\OverridableMethodFinder;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use function Amp\call;

class OverrideMethodProvider implements CodeActionProvider
{
    public function __construct(
        private readonly OverridableMethodFinder $finder,
    ) {
    }

    public function provideActionsFor(TextDocumentItem $item, Range $range, CancellationToken $cancel): Promise
    {
        /** @phpstan-ignore-next-line */
        return call(function () use ($item) {
            $overridables = $this->finder->find(TextDocumentConverter::fromLspTextItem($item));
            if (count($overridables) === 0) {
                return [];
            }
            $actions = [];
            $actions[] = CodeAction::fromArray([
                'title' => sprintf(
                    'Override one of %d methods',
                    count($overridables),
                ),
                'kind' => 'quickfix.override_method',
                'isPreferred' => false,
                'command' => new Command(
                    'Override method dialogue',
                    OverrideMethodCommand::NAME,
                    [
                        $item->uri,
                    ]
                )
            ]);

            return $actions;
        });
    }

    public function kinds(): array
    {
        return [
            'quickfix.override_method'
        ];
    }

    public function name(): string
    {
        return 'override-method';
    }

    public function describe(): string
    {
        return 'override method from parent class';
    }
}
