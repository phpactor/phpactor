<?php

namespace Phpactor\Extension\LanguageServerCallHierarchy\Tests\Unit\Handler;

use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\LanguageServerProtocol\CallHierarchyIncomingCall;
use Phpactor\LanguageServerProtocol\CallHierarchyItem;
use Phpactor\LanguageServerProtocol\CallHierarchyOutgoingCall;
use Phpactor\LanguageServerProtocol\CallHierarchyIncomingCallsRequest;
use Phpactor\LanguageServerProtocol\CallHierarchyOutgoingCallsRequest;
use Phpactor\LanguageServerProtocol\CallHierarchyPrepareRequest;
use Phpactor\LanguageServerProtocol\SymbolKind;
use Phpactor\Extension\LanguageServerCallHierarchy\Handler\CallHierarchyHandler;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\ReferenceFinder\ChainReferenceFinder;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\WorseReflection\Core\Cache\NullCache;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReferenceFinder\MethodCallReferenceFinder;
use Phpactor\WorseReferenceFinder\TolerantVariableReferenceFinder;
use Phpactor\WorseReferenceFinder\WorseReflectionDefinitionLocator;
use PHPUnit\Framework\TestCase;

class CallHierarchyHandlerTest extends TestCase
{
    const EXAMPLE_URI = 'file:///test';
    const CONSUMER_URI = 'file:///consumer.php';
    const EXAMPLE_TEXT_PHP = <<<'PHP'
        <?php

        class MessageProcessor
        {
            public function processMessages(array $messages): void
            {
                $this->validate($messages);
                $this->send($messages);
                log('processed');
            }
        }
        PHP;
    const CONSUMER_TEXT_PHP = <<<'PHP'
        <?php

        class MessageConsumer
        {
            public function consume(): void
            {
                $processor->processMessages([]);
            }
        }
        PHP;

    public function testPrepareCallHierarchyReturnsItemWhenSymbolKnown(): void
    {
        $tester = $this->createTester();
        $response = $tester->requestAndWait(CallHierarchyPrepareRequest::METHOD, [
            'textDocument' => ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_URI),
            'position' => ProtocolFactory::position(4, 20),
        ]);

        self::assertNotNull($response);
        $items = $response->result;
        self::assertIsArray($items);
        self::assertCount(1, $items);
        self::assertInstanceOf(CallHierarchyItem::class, $items[0]);
        self::assertSame('processMessages', $items[0]->name);
        self::assertSame(SymbolKind::METHOD, $items[0]->kind);
    }

    public function testPrepareCallHierarchyReturnsNullWhenSymbolUnknown(): void
    {
        $tester = $this->createTester();
        $response = $tester->requestAndWait(CallHierarchyPrepareRequest::METHOD, [
            'textDocument' => ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_URI),
            'position' => ProtocolFactory::position(0, 0),
        ]);

        self::assertNotNull($response);
        self::assertNull($response->result);
    }

    public function testIncomingCalls(): void
    {
        $tester = $this->createTester();
        $tester->textDocument()->open(self::CONSUMER_URI, self::CONSUMER_TEXT_PHP);

        $item = $this->processMessagesItem();
        $response = $tester->requestAndWait(CallHierarchyIncomingCallsRequest::METHOD, ['item' => $item]);

        self::assertNotNull($response);
        $calls = $response->result;
        self::assertIsArray($calls);
        self::assertCount(1, $calls);
        self::assertInstanceOf(CallHierarchyIncomingCall::class, $calls[0]);
        self::assertSame('consume', $calls[0]->from->name);
        self::assertSame(self::CONSUMER_URI, $calls[0]->from->uri);
    }

    public function testOutgoingCalls(): void
    {
        $tester = $this->createTester();
        $item = $this->processMessagesItem();
        $response = $tester->requestAndWait(CallHierarchyOutgoingCallsRequest::METHOD, ['item' => $item]);

        self::assertNotNull($response);
        $calls = $response->result;
        self::assertIsArray($calls);
        self::assertCount(3, $calls);
        $names = [];
        $kinds = [];
        foreach ($calls as $call) {
            self::assertInstanceOf(CallHierarchyOutgoingCall::class, $call);
            $names[] = $call->to->name;
            $kinds[] = $call->to->kind;
        }
        self::assertSame(['$this->validate', '$this->send', 'log'], $names);
        self::assertSame([SymbolKind::METHOD, SymbolKind::METHOD, SymbolKind::FUNCTION], $kinds);
    }

    private function createTester(): LanguageServerTester
    {
        $builder = LanguageServerTesterBuilder::create();
        $workspace = $builder->workspace();
        $astProvider = new TolerantAstProvider();
        $reflector = ReflectorBuilder::create()->build();
        $referenceFinder = new ChainReferenceFinder([
            new TolerantVariableReferenceFinder($astProvider),
            new MethodCallReferenceFinder($astProvider, $workspace),
        ]);
        $definitionLocator = new WorseReflectionDefinitionLocator($reflector, new NullCache());
        $builder->addHandler(
            new CallHierarchyHandler(
                $workspace,
                $reflector,
                $referenceFinder,
                $astProvider,
                $definitionLocator,
            )
        );
        $tester = $builder->build();
        $tester->textDocument()->open(self::EXAMPLE_URI, self::EXAMPLE_TEXT_PHP);
        return $tester;
    }

    private function processMessagesItem(): CallHierarchyItem
    {
        $doc = TextDocumentBuilder::create(self::EXAMPLE_TEXT_PHP)
            ->language('php')
            ->uri(self::EXAMPLE_URI)
            ->build();
        $bodyRange = $this->methodBodyRange($doc);
        $selectionRange = RangeConverter::toLspRange($bodyRange, self::EXAMPLE_TEXT_PHP);
        return new CallHierarchyItem(
            'processMessages',
            SymbolKind::METHOD,
            self::EXAMPLE_URI,
            $selectionRange,
            $selectionRange
        );
    }

    private function methodBodyRange(TextDocument $doc): ByteOffsetRange
    {
        $ast = (new TolerantAstProvider())->get($doc);
        foreach ($ast->getDescendantNodes() as $node) {
            if (!$node instanceof MethodDeclaration) {
                continue;
            }
            if (null === $node->name || 'processMessages' !== $node->name->getText((string) $doc)) {
                continue;
            }
            $body = $node->compoundStatementOrSemicolon;
            if ($body instanceof CompoundStatementNode) {
                return ByteOffsetRange::fromInts($body->getStartPosition(), $body->getEndPosition());
            }
        }
        return ByteOffsetRange::fromInts(0, 0);
    }
}
