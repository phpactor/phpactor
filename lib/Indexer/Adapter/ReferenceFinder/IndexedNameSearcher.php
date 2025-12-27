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
    public function __construct(private readonly SearchClient $client)
    {
    }

    /**
     * @param null|NameSearcherType::* $type
     */
    public function search(string $name, ?string $type = null): Generator
    {
        if (false === PhpNameMatcher::isPhpFqn($name)) {
            return;
        }

        $fullyQualified = str_starts_with($name, '\\');

        $criteria = $fullyQualified ? Criteria::fqnBeginsWith(substr($name, 1)) : Criteria::shortNameBeginsWith($name);

        $typeCriteria = $this->resolveTypeCriteria($type);

        if (null !== $typeCriteria) {
            $criteria = Criteria::and(
                $criteria,
                Criteria::or(
                    $typeCriteria,

                    // B/C for old indexes
                    Criteria::isClassTypeUndefined(),
                ),
            );
        }

        foreach ($this->client->search($criteria) as $result) {
            yield NameSearchResult::create(
                $result->recordType(),
                FullyQualifiedName::fromString($result->identifier()),
                $result instanceof HasPath ? TextDocumentUri::fromString($result->filepath()) : null,
            );
        }
    }

    /**
     * @param null|NameSearcherType::* $type
     */
    private function resolveTypeCriteria(?string $type): ?Criteria
    {
        return match($type) {
            NameSearcherType::ATTRIBUTE => Criteria::isAttribute(),
            NameSearcherType::ATTRIBUTE_TARGET_CLASS => Criteria::isClassAttribute(),
            NameSearcherType::ATTRIBUTE_TARGET_CLASS_CONSTANT => Criteria::isClassConstantAttribute(),
            NameSearcherType::ATTRIBUTE_TARGET_PROPERTY => Criteria::isPropertyAttribute(),
            NameSearcherType::ATTRIBUTE_TARGET_PARAMETER => Criteria::isParameterAttribute(),
            NameSearcherType::ATTRIBUTE_TARGET_METHOD => Criteria::isMethodAttribute(),
            NameSearcherType::ATTRIBUTE_TARGET_FUNCTION => Criteria::isFunctionAttribute(),
            NameSearcherType::ATTRIBUTE_TARGET_PROMOTED_PROPERTY => Criteria::isPromotedPropertyAttribute(),
            NameSearcherType::CLASS_ => Criteria::isClassConcrete(),
            NameSearcherType::INTERFACE => Criteria::isClassInterface(),
            NameSearcherType::TRAIT => Criteria::isClassTrait(),
            NameSearcherType::ENUM => Criteria::isClassEnum(),
            default => null,
        };
    }
}
