<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Tests\Unit\Listener;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Phpactor\LanguageServerProtocol\TextDocumentContentChangeIncrementalEvent;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Event\TextDocumentClosed;
use Phpactor\LanguageServer\Event\TextDocumentIncrementallyUpdated;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\Extension\LanguageServerWorseReflection\Listener\IncrementalAstListener;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\IncrementalAstProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\WorseReflection\Core\CacheForDocument;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;

class IncrementalAstListenerTest extends TestCase
{
    const EXAMPLE_URI = 'file:///test';

    #[DataProvider('provideUpdateSession')]
    public function testUpdateSession(string $changesFile, string $text, string $expected, ?int $expectedNumberOfParses = null): void
    {
        $edits = $this->decodeExample($changesFile);

        $workspace = new Workspace();
        $uri = TextDocumentUri::fromString(self::EXAMPLE_URI);
        $workspace->open(new TextDocumentItem($uri->__toString(), 'php', 1, $text));

        $event = self::createEvent($uri, $edits);
        $logger = new TestLogger();
        $provider = $this->createProvider($logger);
        $listener = $this->createListener($provider, $workspace);

        $provider->open(TextDocumentBuilder::create($text)->uri($uri)->build());

        $this->dispatchEvent($listener, $event);

        self::assertEquals($expected, $workspace->get($uri->__toString())->text);
        if (null !== $expectedNumberOfParses) {
            /** @phpstan-ignore-next-line */
            self::assertCount($expectedNumberOfParses, $logger->recordsByLevel['warning'] ?? []);
        }
    }

    /**
     * @return Generator<array{string,string,string}>
     */
    public static function provideUpdateSession(): Generator
    {
        yield [
            __DIR__ . '/example.json',
            <<<'PHP'
                <?php
                        $this->updat

                PHP,
                <<<'PHP'
                    <?php
                            $this->update($event)

                    PHP,
        ];
        yield [
            __DIR__ . '/example2.json',
            <<<'PHP'
                <?php
                  $typeWit
                PHP,
            <<<'PHP'
                <?php
                  $typeWithProperty
                PHP,
            4
        ];

    }

    public function testClose(): void
    {
        $workspace = new Workspace();
        $uri = TextDocumentUri::fromString(self::EXAMPLE_URI);
        $workspace->open(new TextDocumentItem($uri->__toString(), 'php', 1, 'hello'));

        $provider = $this->createProvider();
        $provider->open(TextDocumentBuilder::create('foo')->uri($uri)->build());
        $listener = $this->createListener($provider, $workspace);
        self::assertTrue($workspace->has(self::EXAMPLE_URI));

        // dispatch the text document closed event
        $this->dispatchEvent($listener, new TextDocumentClosed(ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_URI)));

        // document shouild have been removed from the workspace and the AST
        // removed from the provider.
        self::assertFalse($workspace->has(self::EXAMPLE_URI));
        $this->expectExceptionMessage('Document has not been opened');
        $provider->update($uri, []);
    }

    public function testOpen(): void
    {
        $uri = TextDocumentUri::fromString(self::EXAMPLE_URI);
        $event = new TextDocumentOpened(new TextDocumentItem($uri->__toString(), 'php', 1, 'hello'));

        $workspace = new Workspace();
        $provider = $this->createProvider();
        $listener = $this->createListener($provider, $workspace);

        // dispatch the text document closed event
        $this->dispatchEvent($listener, $event);
        self::assertTrue($workspace->has(self::EXAMPLE_URI));
        $provider->update($uri, []);
    }

    private function dispatchEvent(ListenerProviderInterface $listenerProvider, object $event): void
    {
        foreach ($listenerProvider->getListenersForEvent($event) as $listener) {
            assert(is_callable($listener));
            $listener($event);
        }
    }

    /**
     * @param list<TextDocumentContentChangeIncrementalEvent> $events
     */
    private static function createEvent(TextDocumentUri $uri, array $events): TextDocumentIncrementallyUpdated
    {
        return new TextDocumentIncrementallyUpdated(
            new VersionedTextDocumentIdentifier(1, $uri->__toString()),
            $events,
        );
    }

    private function createProvider(LoggerInterface $logger = new NullLogger()): IncrementalAstProvider
    {
        return new IncrementalAstProvider(
            new TolerantAstProvider(),
            CacheForDocument::static(),
            $logger,
        );
    }

    private function createListener(IncrementalAstProvider $provider, Workspace $workspace): IncrementalAstListener
    {
        return new IncrementalAstListener(
            $provider,
            $workspace,
        );
    }

    /**
     * @return list<TextDocumentContentChangeIncrementalEvent>
     */
    private function decodeExample(string $path): array
    {
        /** @var list<array<string,mixed>> */
        $data = (array)json_decode((string)file_get_contents($path), true);

        return array_map(function (array $data) {
            return TextDocumentContentChangeIncrementalEvent::fromArray($data);
        }, $data);
    }
}
