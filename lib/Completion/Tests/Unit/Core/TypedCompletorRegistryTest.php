<?php

namespace Phpactor\Completion\Tests\Unit\Core;

use Prophecy\PhpUnit\ProphecyTrait;
use Phpactor\Completion\Core\ChainCompletor;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\TypedCompletorRegistry;
use Phpactor\Completion\Tests\TestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class TypedCompletorRegistryTest extends TestCase
{
    use ProphecyTrait;
    public function testReturnsCompletorsForAType(): void
    {
        $completor = $this->prophesize(Completor::class);
        $registry = new TypedCompletorRegistry([
            'cucumber' => $completor->reveal(),
        ]);
        $completorForType = $registry->completorForType('cucumber');

        $completor->complete(
            TextDocumentBuilder::create('foo')->build(),
            ByteOffset::fromInt(123)
        )->shouldBeCalled();

        $this->assertSame($completor->reveal(), $completorForType);

        iterator_to_array($completorForType->complete(
            TextDocumentBuilder::create('foo')->build(),
            ByteOffset::fromInt(123)
        ));
    }

    public function testEmptyChainCompletorWhenTypeNotConfigured(): void
    {
        $registry = new TypedCompletorRegistry([
        ]);
        $completorForType = $registry->completorForType('cucumber');

        $this->assertInstanceOf(ChainCompletor::class, $completorForType);

        iterator_to_array($completorForType->complete(
            TextDocumentBuilder::create('foo')->build(),
            ByteOffset::fromInt(123)
        ));
    }
}
