<?php

namespace Phpactor\Extension\LanguageServerIndexer\Model;

use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\ConstantRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;
use Phpactor\Indexer\Model\SearchClient;
use Phpactor\LanguageServerProtocol\Location;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\SymbolInformation;
use Phpactor\LanguageServerProtocol\SymbolKind;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;

final class WorkspaceSymbolProvider
{
    public function __construct(
        private SearchClient $client,
        private TextDocumentLocator $locator,
        private int $limit
    ) {
    }

    /**
     * @return Promise<SymbolInformation[]>
     */
    public function provideFor(string $query): Promise
    {
        return \Amp\call(function () use ($query) {
            $infos = [];
            foreach ($this->client->search(Criteria::shortNameContains($query)) as $count => $record) {
                if ($count >= $this->limit) {
                    break;
                }

                assert($record instanceof Record);
                $infos[] = $this->informationFromRecord($record);
            }

            return array_filter($infos, function (?SymbolInformation $info) {
                return $info !== null;
            });
        });
    }

    private function informationFromRecord(Record $record): ?SymbolInformation
    {
        if ($record instanceof ClassRecord) {
            return new SymbolInformation(
                name: $record->fqn()->__toString(),
                kind: SymbolKind::CLASS_,
                location: new Location(
                    TextDocumentUri::fromString($record->filePath()),
                    new Range(
                        $this->toLspPosition($record->start(), $record->filePath()),
                        $this->toLspPosition($record->start()->add(mb_strlen($record->shortName())), $record->filePath())
                    )
                )
            );
        }

        if ($record instanceof FunctionRecord) {
            return new SymbolInformation(
                name: $record->fqn()->__toString(),
                kind: SymbolKind::FUNCTION,
                location: new Location(
                    TextDocumentUri::fromString($record->filePath()),
                    new Range(
                        $this->toLspPosition($record->start(), $record->filePath()),
                        $this->toLspPosition($record->start()->add(mb_strlen($record->shortName())), $record->filePath())
                    )
                )
            );
        }

        if ($record instanceof ConstantRecord) {
            return new SymbolInformation(
                name: $record->fqn()->__toString(),
                kind: SymbolKind::CONSTANT,
                location: new Location(
                    TextDocumentUri::fromString($record->filePath()),
                    new Range(
                        $this->toLspPosition($record->start(), $record->filePath()),
                        $this->toLspPosition($record->start()->add(mb_strlen($record->shortName())), $record->filePath())
                    )
                )
            );
        }

        return null;
    }

    private function toLspPosition(ByteOffset $offset, string $path): Position
    {
        $textDocument = $this->locator->get(TextDocumentUri::fromString($path));
        return PositionConverter::byteOffsetToPosition(
            $offset,
            $textDocument->__toString()
        );
    }
}
