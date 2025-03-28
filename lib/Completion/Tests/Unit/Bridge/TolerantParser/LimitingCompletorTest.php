<?php

namespace Phpactor\Completion\Tests\Unit\Bridge\TolerantParser;

use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\LimitingCompletor;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\AlwaysQualfifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifiable;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\TestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Prophecy\Prophecy\ObjectProphecy;

class LimitingCompletorTest extends TestCase
{
    const EXAMPLE_SOURCE = '<?php';
    const EXAMPLE_OFFSET = 15;

    private ObjectProphecy $innerCompletor;

    private ObjectProphecy $node;

    protected function setUp(): void
    {
        $this->innerCompletor = $this->prophesize(TolerantCompletor::class);
        $this->node = $this->prophesize(Node::class);
    }

    public function testNoSuggestions(): void
    {
        $this->innerCompletor->complete(
            $this->node->reveal(),
            $this->textDocument(self::EXAMPLE_SOURCE),
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        )->will(function () {
            return true;
            yield;
        });

        $suggestions = $this->create(10)->complete(
            $this->node->reveal(),
            $this->textDocument(self::EXAMPLE_SOURCE),
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        );

        $this->assertCount(0, iterator_to_array($suggestions, false));
        $this->assertTrue($suggestions->getReturn());
    }

    public function testSomeSuggestions(): void
    {
        $suggestions = [
            $this->suggestion('foobar'),
            $this->suggestion('barfoo'),
            $this->suggestion('carfoo'),
        ];

        $this->primeInnerCompletor($suggestions);

        $suggestions = $this->create(10)->complete(
            $this->node->reveal(),
            $this->textDocument(self::EXAMPLE_SOURCE),
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        );

        $this->assertCount(3, iterator_to_array($suggestions, false));
        $this->assertTrue($suggestions->getReturn());
    }

    public function testAppliesLimit(): void
    {
        $suggestions = [
            $this->suggestion('foobar'),
            $this->suggestion('barfoo'),
            $this->suggestion('carfoo'),
        ];

        $this->primeInnerCompletor($suggestions);

        $suggestions = $this->create(2)->complete(
            $this->node->reveal(),
            $this->textDocument(self::EXAMPLE_SOURCE),
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        );

        $this->assertCount(2, iterator_to_array($suggestions, false));
        $this->assertFalse($suggestions->getReturn());
    }

    public function testIsNotCompleteWhenInnerCompleterIsNotComplete(): void
    {
        $suggestions = [
            $this->suggestion('foobar'),
            $this->suggestion('barfoo'),
            $this->suggestion('carfoo'),
        ];

        $this->primeInnerCompletor($suggestions, false);

        $suggestions = $this->create(10)->complete(
            $this->node->reveal(),
            $this->textDocument(self::EXAMPLE_SOURCE),
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        );

        $this->assertCount(3, iterator_to_array($suggestions, false));
        $this->assertFalse($suggestions->getReturn());
    }

    public function testQualifiesNonQualifiableCompletors(): void
    {
        $completor = $this->create(10);
        $node = $this->prophesize(Node::class);

        $qualified = $completor->qualifier()->couldComplete($node->reveal());
        $this->assertSame($node->reveal(), $qualified);
    }

    public function testPassesThroughToInnerQualifier(): void
    {
        $node = $this->prophesize(Node::class);
        $this->innerCompletor->willImplement(TolerantQualifiable::class);
        $this->innerCompletor->qualifier()->willReturn(new AlwaysQualfifier())->shouldBeCalled();
        $completor = $this->create(10);

        $qualified = $completor->qualifier()->couldComplete($node->reveal());
        $this->assertSame($node->reveal(), $qualified);
    }

    private function create(int $limit): LimitingCompletor
    {
        return new LimitingCompletor($this->innerCompletor->reveal(), $limit);
    }

    private function suggestion(string $name): Suggestion
    {
        return Suggestion::create($name);
    }

    /**
    * @param array<Suggestion> $suggestions
    */
    private function primeInnerCompletor(array $suggestions, bool $isComplete = true): void
    {
        $this->innerCompletor->complete(
            $this->node->reveal(),
            $this->textDocument(self::EXAMPLE_SOURCE),
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        )->will(function () use ($suggestions, $isComplete) {
            foreach ($suggestions as $suggestion) {
                yield $suggestion;
            }
            return $isComplete;
        });
    }

    private function textDocument(string $document): TextDocument
    {
        return TextDocumentBuilder::create($document)->build();
    }
}
