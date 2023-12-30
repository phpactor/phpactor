<?php

namespace Phpactor\Completion\Tests\Unit\Core;

use Phpactor\Completion\Core\ChainSignatureHelper;
use Phpactor\Completion\Core\Exception\CouldNotHelpWithSignature;
use Phpactor\Completion\Core\SignatureHelp;
use Phpactor\Completion\Core\SignatureHelper;
use Phpactor\Completion\Tests\TestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class ChainSignatureHelperTest extends TestCase
{
    /**
     * @var ObjectProphecy<LoggerInterface>
     */
    private ObjectProphecy $logger;

    /**
     * @var ObjectProphecy<SignatureHelper>
     */
    private ObjectProphecy $helper1;

    private TextDocument $document;

    private ByteOffset $offset;

    /**
     * @var ObjectProphecy<SignatureHelp>
     */
    private ObjectProphecy $help;

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->helper1 = $this->prophesize(SignatureHelper::class);

        $this->document = TextDocumentBuilder::create('foo')->uri('file:///foo')->language('php')->build();
        $this->offset = ByteOffset::fromInt(1);
        $this->help = $this->prophesize(SignatureHelp::class);
    }

    public function testNoHelpersThrowsException(): void
    {
        $this->expectException(CouldNotHelpWithSignature::class);
        $this->create([])->signatureHelp($this->document, $this->offset);
    }

    public function testHelperCouldNotHelp(): void
    {
        $this->expectException(CouldNotHelpWithSignature::class);
        $this->helper1->signatureHelp($this->document, $this->offset)->willThrow(new CouldNotHelpWithSignature('Foobar'));
        $this->logger->debug('Could not provide signature: "Foobar"')->shouldBeCalled();

        $this->create([
            $this->helper1->reveal(),
        ])->signatureHelp($this->document, $this->offset);
    }

    public function testHelpersSignature(): void
    {
        $this->helper1->signatureHelp($this->document, $this->offset)->willReturn($this->help->reveal());

        $help = $this->create([
            $this->helper1->reveal(),
        ])->signatureHelp($this->document, $this->offset);

        $this->assertSame($this->help->reveal(), $help);
    }

    private function create(array $helpers)
    {
        return new ChainSignatureHelper($this->logger->reveal(), $helpers);
    }
}
