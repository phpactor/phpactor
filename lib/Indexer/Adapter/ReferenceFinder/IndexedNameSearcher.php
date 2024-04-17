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

    /**
     * @param null|NameSearcherType::* $type
     */
    public function search(string $name, ?string $type = null): Generator
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
                $result->recordType(),
                FullyQualifiedName::fromString($result->identifier()),
                $result instanceof HasPath ? TextDocumentUri::fromString($result->filepath()) : null
            );
        }
    }

    /**
     * @param null|NameSearcherType::* $type
     */
    private function resolveTypeCriteria(?string $type): ?Criteria
    {
        // todo before merge check if this condition occurs anywhere and possibly remove it
        if ($type === NameSearcherType::ATTRIBUTE) {
            return Criteria::isAttribute();
        }

        if ($type === NameSearcherType::ATTRIBUTE_TARGET_CLASS) {
            return Criteria::isClassAttribute();
        }

        if ($type === NameSearcherType::ATTRIBUTE_TARGET_CLASS_CONSTANT) {
            return Criteria::isClassConstantAttribute();
        }

        if ($type === NameSearcherType::ATTRIBUTE_TARGET_PROPERTY) {
            return Criteria::isPropertyAttribute();
        }

        if ($type === NameSearcherType::ATTRIBUTE_TARGET_PARAMETER) {
            return Criteria::isParameterAttribute();
        }

        if ($type === NameSearcherType::ATTRIBUTE_TARGET_METHOD) {
            return Criteria::isMethodAttribute();
        }

        if ($type === NameSearcherType::ATTRIBUTE_TARGET_FUNCTION) {
            return Criteria::isFunctionAttribute();
        }

        if ($type === NameSearcherType::ATTRIBUTE_TARGET_PROMOTED_PROPERTY) {
            return Criteria::isPromotedPropertyAttribute();
        }

        if ($type === NameSearcherType::CLASS_) {
            return Criteria::isClassConcrete();
        }

        if ($type === NameSearcherType::INTERFACE) {
            return Criteria::isClassInterface();
        }

        if ($type === NameSearcherType::TRAIT) {
            return Criteria::isClassTrait();
        }
        if ($type === NameSearcherType::ENUM) {
            return Criteria::isClassEnum();
        }

        return null;
    }
}
