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
use Phpactor\LanguageServerProtocol\PrepareCallHierarchyRequest;
use Phpactor\LanguageServerProtocol\SymbolKind;
use Phpactor\Extension\LanguageServerCallHierarchy\Handler\CallHierarchyHandler;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\WorseReflection\Core\Inference\ConcreteFrame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Reflector;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use PHPUnit\Framework\TestCase;

class CallHierarchyHandlerTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_URI = 'file:///test';
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

    /**
     * @var ObjectProphecy<Reflector>
     */
    private ObjectProphecy $reflector;

    /**
     * @var ObjectProphecy<ReferenceFinder>
     */
    private ObjectProphecy $finder;

    /**
     * @var ObjectProphecy<AstProvider>
     */
    private ObjectProphecy $astProvider;

    /**
     * @var ObjectProphecy<DefinitionLocator>
     */
    private ObjectProphecy $locator;

    /**
     * @var ObjectProphecy<FrameResolver>
     */
    private ObjectProphecy $frameResolver;

    protected function setUp(): void
    {
        $this->reflector = $this->prophesize(Reflector::class);
        $this->finder = $this->prophesize(ReferenceFinder::class);
        $this->astProvider = $this->prophesize(AstProvider::class);
        $this->locator = $this->prophesize(DefinitionLocator::class);
        $this->frameResolver = $this->prophesize(FrameResolver::class);
    }

    public function testPrepareCallHierarchyReturnsItemWhenSymbolKnown(): void
    {
        $doc = TextDocumentBuilder::create(self::EXAMPLE_TEXT_PHP)
            ->language('php')
            ->uri(self::EXAMPLE_URI)
            ->build();

        $symbol = Symbol::fromTypeNameAndPosition(Symbol::METHOD, 'processMessages', ByteOffsetRange::fromInts(0, 0));
        $reflection = ReflectionOffset::fromFrameAndSymbolContext(new ConcreteFrame(), NodeContext::for($symbol));
        $this->reflector->reflectOffset(Argument::any(), Argument::any())
            ->willReturn($reflection)
            ->shouldBeCalled();

        $tester = $this->createTester();
        $response = $tester->requestAndWait(PrepareCallHierarchyRequest::METHOD, [
            'textDocument' => ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_URI),
            'position' => ProtocolFactory::position(0, 0),
        ]);

        $items = $response->result;
        $this->assertIsArray($items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(CallHierarchyItem::class, $items[0]);
        $this->assertSame('processMessages', $items[0]->name);
        $this->assertSame(SymbolKind::METHOD, $items[0]->kind);
    }

    public function testPrepareCallHierarchyReturnsNullWhenSymbolUnknown(): void
    {
        $doc = TextDocumentBuilder::create(self::EXAMPLE_TEXT_PHP)
            ->language('php')
            ->uri(self::EXAMPLE_URI)
            ->build();

        $reflection = ReflectionOffset::fromFrameAndSymbolContext(new ConcreteFrame(), NodeContext::for(Symbol::unknown()));
        $this->reflector->reflectOffset(Argument::any(), Argument::any())
            ->willReturn($reflection)
            ->shouldBeCalled();

        $tester = $this->createTester();
        $response = $tester->requestAndWait(PrepareCallHierarchyRequest::METHOD, [
            'textDocument' => ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_URI),
            'position' => ProtocolFactory::position(0, 0),
        ]);

        $this->assertNull($response->result);
    }

    public function testIncomingCalls(): void
    {
        $doc = TextDocumentBuilder::create(self::EXAMPLE_TEXT_PHP)
            ->language('php')
            ->uri(self::EXAMPLE_URI)
            ->build();

        $bodyRange = $this->methodBodyRange($doc);
        $selectionRange = RangeConverter::toLspRange($bodyRange, self::EXAMPLE_TEXT_PHP);
        $item = new CallHierarchyItem(
            'processMessages',
            SymbolKind::METHOD,
            self::EXAMPLE_URI,
            $selectionRange,
            $selectionRange
        );

        $this->finder->findReferences(Argument::any(), Argument::any())
            ->willYield([
                PotentialLocation::surely(new Location($doc->uriOrThrow(), ByteOffsetRange::fromInts(10, 10)))
            ])
            ->shouldBeCalled();

        $tester = $this->createTester();
        $response = $tester->requestAndWait(CallHierarchyIncomingCallsRequest::METHOD, [
            'item' => $item,
        ]);

        $calls = $response->result;
        $this->assertIsArray($calls);
        $this->assertCount(1, $calls);
        $this->assertInstanceOf(CallHierarchyIncomingCall::class, $calls[0]);
        $this->assertSame('unknown', $calls[0]->from->name);
    }

    public function testOutgoingCalls(): void
    {
        $doc = TextDocumentBuilder::create(self::EXAMPLE_TEXT_PHP)
            ->language('php')
            ->uri(self::EXAMPLE_URI)
            ->build();

        $bodyRange = $this->methodBodyRange($doc);
        $selectionRange = RangeConverter::toLspRange($bodyRange, self::EXAMPLE_TEXT_PHP);
        $item = new CallHierarchyItem(
            'processMessages',
            SymbolKind::METHOD,
            self::EXAMPLE_URI,
            $selectionRange,
            $selectionRange
        );

        $this->astProvider->get(Argument::any())
            ->willReturn((new TolerantAstProvider())->get($doc))
            ->shouldBeCalled();

        $this->reflector->walk(Argument::any(), Argument::any())
            ->will(function (array $args) {
                $walker = $args[1];
                $ast = (new TolerantAstProvider())->get($doc);
                $frame = new ConcreteFrame();
                $resolver = $this->frameResolver->reveal();
                foreach ($ast->getDescendantNodes() as $node) {
                    $walker->enter($resolver, $frame, $node);
                    $walker->exit($resolver, $frame, $node);
                    yield null;
                }
            })
            ->shouldBeCalled();

        $this->locator->locateDefinition(Argument::any(), Argument::any())
            ->willReturn(
                TypeLocations::forLocation(
                    new TypeLocation(
                        TypeFactory::class('validate'),
                        new Location($doc->uriOrThrow(), ByteOffsetRange::fromInts(0, 0))
                    )
                )
            )
            ->shouldBeCalledTimes(3);

        $tester = $this->createTester();
        $response = $tester->requestAndWait(CallHierarchyOutgoingCallsRequest::METHOD, [
            'item' => $item,
        ]);

        $calls = $response->result;
        $this->assertIsArray($calls);
        $this->assertCount(3, $calls);
        foreach ($calls as $call) {
            $this->assertInstanceOf(CallHierarchyOutgoingCall::class, $call);
        }
    }

    private function createTester(): LanguageServerTester
    {
        $builder = LanguageServerTesterBuilder::create();
        $builder->addHandler(
            new CallHierarchyHandler(
                $builder->workspace(),
                $this->reflector->reveal(),
                $this->finder->reveal(),
                $this->astProvider->reveal(),
                $this->locator->reveal(),
            )
        );
        $tester = $builder->build();
        $tester->textDocument()->open(self::EXAMPLE_URI, self::EXAMPLE_TEXT_PHP);
        return $tester;
    }

    private function methodBodyRange($doc): ByteOffsetRange
    {
        $ast = (new TolerantAstProvider())->get($doc);
        foreach ($ast->getDescendantNodes() as $node) {
            if (!$node instanceof MethodDeclaration) {
                continue;
            }
            if (null === $node->name || 'processMessages' !== $node->name->getText()) {
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
