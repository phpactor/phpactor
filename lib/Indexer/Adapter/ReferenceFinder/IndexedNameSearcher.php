<?php

namespace Phpactor\Indexer\Adapter\ReferenceFinder;

use Generator;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\HasPath;
use Phpactor\Indexer\Model\SearchClient;
use Phpactor\Name\FullyQualifiedName;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\TextDocument\TextDocumentUri;

class IndexedNameSearcher implements NameSearcher
{
    /**
     * @var SearchClient
     */
    private $client;

    public function __construct(SearchClient $client)
    {
        $this->client = $client;
    }

    public function search(string $name): Generator
    {
        foreach ($this->client->search(Criteria::shortNameBeginsWith($name)) as $result) {
            yield NameSearchResult::create(
                $result->recordType(),
                FullyQualifiedName::fromString($result->identifier()),
                $result instanceof HasPath ? TextDocumentUri::fromString($result->filepath()) : null
            );
        }
    }
}
