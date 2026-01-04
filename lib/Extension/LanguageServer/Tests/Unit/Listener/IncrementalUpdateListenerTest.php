<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\Listener;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServer\Listener\IncrementalUpdateListener;
use Phpactor\LanguageServerProtocol\TextDocumentContentChangeIncrementalEvent;
use Phpactor\LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\ProtocolFactory;

class IncrementalUpdateListenerTest extends TestCase
{
    public function testApplyEdits(): void
    {
        $builder = LanguageServerTesterBuilder::create();
        $workspace = $builder->workspace();
        $builder->build()->textDocument()->open('file:///foo', 'hello');

        (new IncrementalUpdateListener($workspace))->applyEdits(
            new VersionedTextDocumentIdentifier(version: 1, uri: 'file:///foo'),
            [
                new TextDocumentContentChangeIncrementalEvent(
                    ProtocolFactory::range(0, 0, 0, 1),
                    text: 'b',
                ),
                new TextDocumentContentChangeIncrementalEvent(
                    ProtocolFactory::range(0, 5, 0, 5),
                    text: 'b',
                ),
            ],
        );

        self::assertEquals('bellob', $workspace->get('file:///foo')->text);
    }

    public function testApplyEditsMultiline(): void
    {
        $builder = LanguageServerTesterBuilder::create();
        $workspace = $builder->workspace();
        $builder->build()->textDocument()->open('file:///foo', <<<'PHP'
                dump(12341234)
            PHP);

        (new IncrementalUpdateListener($workspace))->applyEdits(
            new VersionedTextDocumentIdentifier(version: 1, uri: 'file:///foo'),
            [
                new TextDocumentContentChangeIncrementalEvent(
                    ProtocolFactory::range(0, 9, 0, 17),
                    text: "\n43214321",
                ),
                new TextDocumentContentChangeIncrementalEvent(
                    ProtocolFactory::range(1, 0, 1, 0),
                    text: '        ',
                ),
            ],
        );

        self::assertEquals(<<<'PHP'
                dump(
                    43214321)
            PHP, $workspace->get('file:///foo')->text);
    }
}
