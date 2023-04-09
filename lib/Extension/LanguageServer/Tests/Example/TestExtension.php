<?php

namespace Phpactor\Extension\LanguageServer\Tests\Example;

use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServer\Container\DiagnosticProviderTag;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\MessageType;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Command\Command as CoreCommand;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use Phpactor\MapResolver\Resolver;

class TestExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('test.handler', function (Container $container) {
            return new class implements Handler {
                public function methods(): array
                {
                    return ['test' => 'test'];
                }

                public function test()
                {
                    return new Success(new NotificationMessage('window/showMessage', [
                        'type' => MessageType::INFO,
                        'message' => 'Hallo',
                    ]));
                }
            };
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => []]);

        $container->register('test.service', function (Container $container) {
            return new class($container->get(ClientApi::class)) implements ServiceProvider {
                public function __construct(private ClientApi $api)
                {
                }

                public function services(): array
                {
                    return ['test'];
                }

                public function test()
                {
                    $this->api->window()->showmessage()->info('service started');
                    return new Success(new NotificationMessage('window/showMessage', [
                        'type' => MessageType::INFO,
                        'message' => 'Hallo',
                    ]));
                }
            };
        }, [ LanguageServerExtension::TAG_SERVICE_PROVIDER => []]);

        $container->register('test.command', function (Container $container) {
            return new class implements CoreCommand {
                public function __invoke(string $text): Promise
                {
                    return new Success($text);
                }
            };
        }, [
            LanguageServerExtension::TAG_COMMAND => [
                'name' => 'echo',
            ],
        ]);

        $container->register('test.code_action_provider', function (Container $container) {
            return new class implements CodeActionProvider {
                public function describe(): string
                {
                    return 'foobar';
                }
                public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
                {
                    return new Success([
                        CodeAction::fromArray([
                            'title' => 'Alice',
                            'command' => new Command('Hello Alice', 'phpactor.say_hello', [
                                'Alice',
                            ])
                        ]),
                        CodeAction::fromArray([
                            'title' => 'Bob',
                            'command' => new Command('Hello Bob', 'phpactor.say_hello', [
                                'Bob',
                            ])
                        ])
                    ]);
                }


                public function kinds(): array
                {
                    return ['example'];
                }
            };
        }, [ LanguageServerExtension::TAG_CODE_ACTION_PROVIDER => []]);

        $container->register('test.diagnostic_provider', function (Container $container) {
            return new class implements DiagnosticsProvider {
                /**
                 * @return Promise<array<Diagnostic>>
                 */
                public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
                {
                    return new Success([
                    ]);
                }

                public function name(): string
                {
                    return 'dp1';
                }
            };
        }, [ LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create('dp1', false)]);

        $container->register('test.diagnostic_provider.outsourced', function (Container $container) {
            return new class implements DiagnosticsProvider {
                /**
                 * @return Promise<array<Diagnostic>>
                 */
                public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
                {
                    return new Success([
                    ]);
                }

                public function name(): string
                {
                    return 'dp2';
                }
            };
        }, [ LanguageServerExtension::TAG_DIAGNOSTICS_PROVIDER => DiagnosticProviderTag::create('dp2.outsourced', true)]);
    }


    public function configure(Resolver $schema): void
    {
    }
}
