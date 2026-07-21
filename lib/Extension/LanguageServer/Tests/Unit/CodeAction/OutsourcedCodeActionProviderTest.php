<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\CodeAction;

use Amp\CancellationTokenSource;
use Amp\Success;
use Phpactor\Extension\LanguageServer\CodeAction\ClosureCodeActionProvider;
use Phpactor\Extension\LanguageServer\CodeAction\OutsourcedCodeActionProvider;
use Phpactor\Extension\LanguageServer\Tests\Unit\LanguageServerTestCase;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServer\Core\CodeAction\AggregateCodeActionProvider;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Psr\Log\Test\TestLogger;
use function Amp\Promise\wait;

class OutsourcedCodeActionProviderTest extends LanguageServerTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testLogMessageWhenTokenIsAlreadyCancelled(): void
    {
        $logger = new TestLogger();
        $provider = new OutsourcedCodeActionProvider(
            [
                __DIR__ . '/../../../../../../bin/phpactor',
                'language-server:code-action',
            ],
            $this->workspace()->path(),
            $logger,
            new AggregateCodeActionProvider(
                new ClosureCodeActionProvider(function () {
                    return new Success([new CodeAction(title: 'hello')]);
                })
            ),
        );
        $cancelSource = (new CancellationTokenSource());
        $cancelSource->cancel();
        $codeActions = wait($provider->provideActionsFor(
            ProtocolFactory::textDocumentItem('file:///foo', '<?php Foobar::class;'),
            ProtocolFactory::range(1, 2, 3, 4),
            $cancelSource->getToken()
        ));

        self::assertCount(0, $codeActions);
        /** @phpstan-ignore-next-line */
        self::assertStringContainsString('Could not write to stdin', $logger->recordsByLevel['debug'][0]['message']);
    }
}
