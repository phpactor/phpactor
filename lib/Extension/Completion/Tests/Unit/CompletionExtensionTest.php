<?php

namespace Phpactor\Extension\Completion\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\LabelFormatter;
use Phpactor\Completion\Core\SignatureHelp;
use Phpactor\Completion\Core\SignatureHelper;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Core\TypedCompletorRegistry;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;

class CompletionExtensionTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_SUGGESTION = 'example_suggestion';
    const EXAMPLE_SOURCE = 'asd';
    const EXAMPLE_OFFSET = 1234;

    private ObjectProphecy $completor1;

    private ObjectProphecy $formatter1;

    private ObjectProphecy $signatureHelper1;

    public function setUp(): void
    {
        $this->completor1 = $this->prophesize(Completor::class);
        $this->signatureHelper1 = $this->prophesize(SignatureHelper::class);
        $this->formatter1 = $this->prophesize(Formatter::class);
    }

    public function testCreatesChainedCompletor(): void
    {
        $document = TextDocumentBuilder::create(self::EXAMPLE_SOURCE)->build();
        $this->completor1->complete(
            $document,
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        )->will(function () {
            return (function () {
                yield Suggestion::create(self::EXAMPLE_SUGGESTION);
            })();
        });

        $completor = $this
            ->createContainer()
            ->expect(CompletionExtension::SERVICE_REGISTRY, TypedCompletorRegistry::class)
            ->completorForType('php');
        $results = iterator_to_array($completor->complete(
            $document,
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        ));

        $this->assertEquals(self::EXAMPLE_SUGGESTION, $results[0]->name());
    }

    public function testCreatesFormatterFromEitherSingleFormatterOrArray(): void
    {
        $object = new stdClass();
        $this->formatter1->canFormat($object)->shouldBeCalledTimes(3)->willReturn(false);

        $formatter = $this->createContainer()->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER);
        $canFormat = $formatter->canFormat($object);
        $this->assertEquals(false, $canFormat);
    }

    public function testCreatesSignatureHelper(): void
    {
        $document = TextDocumentBuilder::create(self::EXAMPLE_SOURCE)->build();
        $this->signatureHelper1->signatureHelp(
            $document,
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        )->will(function () {
            return (function () {
                return new SignatureHelp([], 0);
            })();
        });

        $signatureHelper = $this->createContainer()->get(CompletionExtension::SERVICE_SIGNATURE_HELPER);
        $help = $signatureHelper->signatureHelp(
            $document,
            ByteOffset::fromInt(self::EXAMPLE_OFFSET)
        );

        $this->assertInstanceOf(SignatureHelp::class, $help);
    }

    private function createContainer(): Container
    {
        $builder = new PhpactorContainer();
        $extension = new CompletionExtension();

        $builder->register('completor1', function () {
            return $this->completor1->reveal();
        }, [ CompletionExtension::TAG_COMPLETOR => []]);

        $builder->register('formatter', function () {
            return $this->formatter1->reveal();
        }, [ CompletionExtension::TAG_SHORT_DESC_FORMATTER => []]);

        $builder->register('signarure_helper', function () {
            return $this->signatureHelper1->reveal();
        }, [ CompletionExtension::TAG_SIGNATURE_HELPER => []]);

        $builder->register('short_desc_formatter_array', function () {
            return [
                $this->formatter1->reveal(),
                $this->formatter1->reveal(),
            ];
        }, [ CompletionExtension::TAG_SHORT_DESC_FORMATTER => []]);

        $builder->register('snippet_formatter_array', function () {
            return [
                $this->formatter1->reveal(),
                $this->formatter1->reveal(),
            ];
        }, [ CompletionExtension::TAG_SNIPPET_FORMATTER => []]);

        $extension->load($builder);

        $extension = new LoggingExtension();
        $extension->load($builder);
        return $builder->build([
            'logging.enabled' => false,
            CompletionExtension::PARAM_DEDUPE => false,
            CompletionExtension::PARAM_DEDUPE_MATCH_FQN => false,
            CompletionExtension::PARAM_LIMIT => 10,
            CompletionExtension::PARAM_LABEL_FORMATTER => LabelFormatter::HELPFUL,
        ]);
    }
}
