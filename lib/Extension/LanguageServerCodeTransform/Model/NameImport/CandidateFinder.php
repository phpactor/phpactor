<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport;

use Generator;
use Phpactor\CodeTransform\Domain\Helper\UnresolvableClassNameFinder;
use Phpactor\CodeTransform\Domain\NameWithByteOffsets;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\ConstantRecord;
use Phpactor\Indexer\Model\Record\HasFullyQualifiedName;
use Phpactor\Indexer\Model\SearchClient;
use Phpactor\CodeTransform\Domain\NameWithByteOffset;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Reflector\FunctionReflector;
use Phpactor\WorseReflection\Core\Exception\NotFound;

class CandidateFinder
{
    private UnresolvableClassNameFinder $finder;

    private SearchClient $client;

    private bool $importGlobals;

    private FunctionReflector $functionReflector;

    public function __construct(UnresolvableClassNameFinder $finder, FunctionReflector $functionReflector, SearchClient $client, bool $importGlobals = false)
    {
        $this->finder = $finder;
        $this->client = $client;
        $this->importGlobals = $importGlobals;
        $this->functionReflector = $functionReflector;
    }

    public function unresolved(TextDocumentItem $item): NameWithByteOffsets
    {
        $names = $this->finder->find(
            TextDocumentBuilder::create($item->text)->uri($item->uri)->language('php')->build()
        );
        return new NameWithByteOffsets(...array_filter(iterator_to_array($names), function (NameWithByteOffset $unresolvedName) {
            if ($this->isUnresolvedGlobalFunction($unresolvedName)) {
                if (false === $this->importGlobals) {
                    return false;
                }
            }
            return true;
        }));
    }

    /**
     * @return Generator<NameCandidate>
     */
    public function importCandidates(
        TextDocumentItem $item
    ): Generator {
        $seen = [];
        foreach ($this->unresolved($item) as $unresolvedName) {
            assert($unresolvedName instanceof NameWithByteOffset);
            $nameString = (string)$unresolvedName->name();
            if (isset($seen[$nameString])) {
                continue;
            }
            $seen[$nameString] = true;
            foreach ($this->candidatesForUnresolvedName($unresolvedName) as $candidate) {
                assert($candidate instanceof NameCandidate);
                $nameString = (string)$candidate->candidateFqn();
                if (isset($seen[$nameString])) {
                    continue;
                }
                $seen[$nameString] = true;
                yield $candidate;
            }
        }
    }

    public function candidatesForUnresolvedName(NameWithByteOffset $unresolvedName): Generator
    {
        if ($this->isUnresolvedGlobalFunction($unresolvedName)) {
            if (false === $this->importGlobals) {
                return;
            }
            yield new NameCandidate($unresolvedName, $unresolvedName->name()->head()->__toString());
            return;
        }
        assert($unresolvedName instanceof NameWithByteOffset);
        
        $candidates = $this->findCandidates($unresolvedName);
        
        foreach ($candidates as $candidate) {
            assert($candidate instanceof HasFullyQualifiedName);
        
            // skip constants for now
            if ($candidate instanceof ConstantRecord) {
                continue;
            }
        
            $fqn = $candidate->fqn()->__toString();
            yield new NameCandidate($unresolvedName, $candidate->fqn());
        }
    }

    private function isUnresolvedGlobalFunction(NameWithByteOffset $unresolvedName): bool
    {
        if ($unresolvedName->type() !== NameWithByteOffset::TYPE_FUNCTION) {
            return false;
        }

        try {
            $s = $this->functionReflector->sourceCodeForFunction(
                $unresolvedName->name()->head()->__toString()
            );
            return true;
        } catch (NotFound $notFound) {
        }

        return false;
    }

    private function findCandidates(NameWithByteOffset $unresolvedName): array
    {
        $candidates = [];
        foreach ($this->client->search(Criteria::and(
            Criteria::or(
                Criteria::isConstant(),
                Criteria::isClass(),
                Criteria::isFunction()
            ),
            Criteria::exactShortName($unresolvedName->name()->head()->__toString())
        )) as $candidate) {
            $candidates[] = $candidate;
        }
        return $candidates;
    }
}
