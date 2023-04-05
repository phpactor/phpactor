<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests\Unit\LspCommand;

use Amp\Success;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\NameWithByteOffset;
use Phpactor\CodeTransform\Domain\NameWithByteOffsets;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ImportAllUnresolvedNamesCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ImportNameCommand;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\CandidateFinder;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\NameCandidate;
use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\Name\FullyQualifiedName;
use Phpactor\TextDocument\ByteOffset;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use function Amp\Promise\wait;

class ImportAllUnresolvedNamesCommandTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_URI = 'file:///foobar';
    const EXAMPLE_CANDIDATE = 'Foobar';

    private ObjectProphecy $candidateFinder;

    private ObjectProphecy $importName;

    public function setUp(): void
    {
        $this->candidateFinder = $this->prophesize(CandidateFinder::class);
        $this->importName = $this->prophesize(ImportNameCommand::class);
    }

    public function testNoUnresolvedNamesDoesNothing(): void
    {
        $builder = $this->createBuilder();
        $server = $builder->build();
        $server->textDocument()->open(self::EXAMPLE_URI, 'foobar');

        $this->candidateFinder->unresolved($builder->workspace()->get(self::EXAMPLE_URI))->willReturn(new Success(new NameWithByteOffsets()));

        wait($server->workspace()->executeCommand(ImportAllUnresolvedNamesCommand::NAME, [
            self::EXAMPLE_URI
        ]));
        $this->addToAssertionCount(1);
    }

    public function testNoCandidates(): void
    {
        $builder = $this->createBuilder();
        $server = $builder->build();
        $server->textDocument()->open(self::EXAMPLE_URI, 'foobar');

        $unresolvedName = $this->createUnresolvedName();
        $this->candidateFinder->unresolved($builder->workspace()->get(self::EXAMPLE_URI))->willReturn(new Success(new NameWithByteOffsets(
            $unresolvedName
        )));
        $this->candidateFinder->candidatesForUnresolvedName($unresolvedName)->willYield([]);

        wait($server->workspace()->executeCommand(ImportAllUnresolvedNamesCommand::NAME, [
            self::EXAMPLE_URI
        ]));

        $notification = $server->transmitter()->shiftNotification();
        self::assertEquals('Class "Foobar" has no candidates', $notification->params['message']);
    }

    public function testIdenticallyNamedCandidates(): void
    {
        $builder = $this->createBuilder();
        $server = $builder->build();
        $server->textDocument()->open(self::EXAMPLE_URI, 'foobar');

        $unresolvedName = $this->createUnresolvedName();
        $this->candidateFinder->unresolved($builder->workspace()->get(self::EXAMPLE_URI))->willReturn(new Success(new NameWithByteOffsets(
            $unresolvedName,
            $this->createUnresolvedName()
        )));
        $this->candidateFinder->candidatesForUnresolvedName($unresolvedName)->willYield([]);

        wait($server->workspace()->executeCommand(ImportAllUnresolvedNamesCommand::NAME, [
            self::EXAMPLE_URI
        ]));

        $notification = $server->transmitter()->shiftNotification();
        $notification = $server->transmitter()->shiftNotification();
        self::assertNull($notification);
    }

    public function testOneCandidate(): void
    {
        $builder = $this->createBuilder();
        $server = $builder->build();
        $server->textDocument()->open(self::EXAMPLE_URI, 'foobar');

        $unresolvedName = $this->createUnresolvedName();
        $this->candidateFinder->unresolved($builder->workspace()->get(self::EXAMPLE_URI))->willReturn(new Success(new NameWithByteOffsets(
            $unresolvedName
        )));
        $this->candidateFinder->candidatesForUnresolvedName($unresolvedName)->willYield([
            new NameCandidate($unresolvedName, self::EXAMPLE_CANDIDATE)
        ]);
        $this->importName->__invoke(Argument::cetera())->willReturn(new Success(true))->shouldBeCalled();

        wait($server->workspace()->executeCommand(ImportAllUnresolvedNamesCommand::NAME, [
            self::EXAMPLE_URI
        ]));
    }

    public function testAsksUserToSelectFromMultipleCandidates(): void
    {
        $builder = $this->createBuilder();
        $server = $builder->build();
        $server->textDocument()->open(self::EXAMPLE_URI, 'foobar');

        $unresolvedName = $this->createUnresolvedName();
        $this->candidateFinder->unresolved($builder->workspace()->get(self::EXAMPLE_URI))->willReturn(new Success(new NameWithByteOffsets(
            $unresolvedName
        )));
        $this->candidateFinder->candidatesForUnresolvedName($unresolvedName)->willYield([
            new NameCandidate($unresolvedName, self::EXAMPLE_CANDIDATE),
            new NameCandidate($unresolvedName, 'Barfoo')
        ]);
        $this->importName->__invoke(Argument::cetera())->willReturn(new Success(true))->shouldBeCalled();

        $promise = $server->workspace()->executeCommand(ImportAllUnresolvedNamesCommand::NAME, [
            self::EXAMPLE_URI
        ]);
        $builder->responseWatcher()->resolveLastResponse(new MessageActionItem(self::EXAMPLE_CANDIDATE));
        wait($promise);
    }

    private function createBuilder(): LanguageServerTesterBuilder
    {
        $builder = LanguageServerTesterBuilder::createBare()
            ->enableCommands()
            ->enableTextDocuments();

        $builder->addCommand(
            ImportAllUnresolvedNamesCommand::NAME,
            new ImportAllUnresolvedNamesCommand(
                $this->candidateFinder->reveal(),
                $builder->workspace(),
                $this->importName->reveal(),
                $builder->clientApi()
            )
        );
        return $builder;
    }

    private function createUnresolvedName(): NameWithByteOffset
    {
        return new NameWithByteOffset(FullyQualifiedName::fromString(self::EXAMPLE_CANDIDATE), ByteOffset::fromInt(10), NameWithByteOffset::TYPE_CLASS);
    }
}
