<?php

namespace Phpactor\Indexer\Adapter\ReferenceFinder;

use Generator;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\HasPath;
use Phpactor\Indexer\Model\SearchClient;
use Phpactor\Indexer\Util\PhpNameMatcher;
use Phpactor\Name\FullyQualifiedName;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\NameSearcherType;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\TextDocument\TextDocumentUri;

class IndexedNameSearcher implements NameSearcher
{
    public function __construct(private SearchClient $client)
    {
    }

    public function search(string $name, ?NameSearcherType $type = null): Generator
    {
        if (false === PhpNameMatcher::isPhpFqn($name)) {
            return;
        }

        $fullyQualified = str_starts_with($name, '\\');
        if ($fullyQualified) {
            $criteria = Criteria::fqnBeginsWith(substr($name, 1));
        } else {
            $criteria = Criteria::shortNameBeginsWith($name);
        }

        $typeCriteria = $this->resolveTypeCriteria($type);

        if ($typeCriteria) {
            $criteria = Criteria::and(
                $criteria,
                Criteria::or(
                    $typeCriteria,

                    // B/C for old indexes
                    Criteria::isClassTypeUndefined()
                )
            );
        }

        foreach ($this->client->search($criteria) as $result) {
            yield NameSearchResult::create(
                $result->recordType()->value,
                FullyQualifiedName::fromString($result->identifier()),
                $result instanceof HasPath ? TextDocumentUri::fromString($result->filepath()) : null
            );
        }
    }

    private function resolveTypeCriteria(?NameSearcherType $type): ?Criteria
    {
        return match ($type) {
            NameSearcherType::ATTRIBUTE =>  Criteria::isAttribute(),
            NameSearcherType::CLASS_ =>  Criteria::isClassConcrete(),
            NameSearcherType::INTERFACE =>  Criteria::isClassInterface(),
            NameSearcherType::TRAIT =>  Criteria::isClassTrait(),
            NameSearcherType::ENUM =>  Criteria::isClassEnum(),
            default => null,
        };
    }
}
