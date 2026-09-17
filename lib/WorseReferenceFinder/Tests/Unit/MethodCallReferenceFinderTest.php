<?php

namespace Phpactor\WorseReferenceFinder\Tests\Unit;

use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\WorseReferenceFinder\MethodCallReferenceFinder;
use PHPUnit\Framework\TestCase;
use function iterator_to_array;

class MethodCallReferenceFinderTest extends TestCase
{
    private const URI_A = 'file:///a.php';
    private const URI_B = 'file:///b.php';
    private const SOURCE_A = <<<'PHP'
        <?php
        class MessageProcessor
        {
            public function processMessages(array $messages): void
            {
                $this->validate($messages);
                $this->send($messages);
            }
        }
        PHP;
    private const SOURCE_B = <<<'PHP'
        <?php
        class MessageConsumer
        {
            public function run(MessageProcessor $processor): void
            {
                $processor->processMessages([]);
            }
        }
        PHP;

    public function testFindsMethodCallReferencesAcrossDocuments(): void
    {
        $workspace = new Workspace();
        $workspace->open(new TextDocumentItem(self::URI_A, 'php', 1, self::SOURCE_A));
        $workspace->open(new TextDocumentItem(self::URI_B, 'php', 1, self::SOURCE_B));

        $document = TextDocumentBuilder::create(self::SOURCE_A)
            ->uri(self::URI_A)
            ->language('php')
            ->build();

        $finder = new MethodCallReferenceFinder(new TolerantAstProvider(), $workspace);
        // Byte offset of the `processMessages` name token in SOURCE_A.
        $references = iterator_to_array($finder->findReferences($document, ByteOffset::fromInt(51)), false);

        self::assertCount(1, $references);
        $location = $references[0]->location();
        self::assertSame(self::URI_B, (string)$location->uri());
        self::assertTrue($location->range()->start()->toInt() > 0);
    }

    public function testYieldsNothingWhenMethodNameUnresolvable(): void
    {
        $workspace = new Workspace();
        $workspace->open(new TextDocumentItem(self::URI_A, 'php', 1, self::SOURCE_A));

        $document = TextDocumentBuilder::create(self::SOURCE_A)
            ->uri(self::URI_A)
            ->language('php')
            ->build();

        $finder = new MethodCallReferenceFinder(new TolerantAstProvider(), $workspace);
        // Offset on `$messages` parameter, not on a method name.
        $references = iterator_to_array($finder->findReferences($document, ByteOffset::fromInt(99)), false);

        self::assertCount(0, $references);
    }
}
