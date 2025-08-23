<?php

namespace Phpactor\Extension\LanguageServerIndexer\Model;

use function Amp\call;
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
        return call(function () use ($query) {
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
        $kind = match (true) {
            $record instanceof ClassRecord => SymbolKind::CLASS_,
            $record instanceof FunctionRecord => SymbolKind::FUNCTION,
            $record instanceof ConstantRecord => SymbolKind::CONSTANT,
            default => null
        };

        if ($kind === null) {
            return null;
        }

        /** @var ClassRecord|FunctionRecord|ConstantRecord $record */

        $uri = TextDocumentUri::fromString($record->filePath());

        return new SymbolInformation(
            name: $record->fqn()->__toString(),
            kind: $kind,
            location: new Location(
                $uri,
                new Range(
                    $this->toLspPosition($record->start(), $uri),
                    $this->toLspPosition($record->start()->add(mb_strlen($record->shortName())), $uri)
                )
            )
        );
    }

    private function toLspPosition(ByteOffset $offset, TextDocumentUri $uri): Position
    {
        return PositionConverter::byteOffsetToPosition(
            $offset,
            $this->locator->get($uri)->__toString()
        );
    }
}
