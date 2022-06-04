<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport;

use Generator;
use Phpactor\CodeTransform\Domain\NameWithByteOffsets;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\ConstantRecord;
use Phpactor\Indexer\Model\Record\HasFullyQualifiedName;
use Phpactor\Indexer\Model\SearchClient;
use Phpactor\CodeTransform\Domain\NameWithByteOffset;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnresolvableNameDiagnostic;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Reflector;

class CandidateFinder
{
    private SearchClient $client;

    private bool $importGlobals;

    private Reflector $reflector;

    public function __construct(Reflector $reflector, SearchClient $client)
    {
        $this->client = $client;
        $this->reflector = $reflector;
    }

    public function unresolved(TextDocumentItem $item): NameWithByteOffsets
    {
        $diagnostics = $this->reflector->diagnostics($item->text)->byClass(UnresolvableNameDiagnostic::class);

        return new NameWithByteOffsets(...array_map(function (UnresolvableNameDiagnostic $diagnostic) {
            return new NameWithByteOffset($diagnostic->name(), $diagnostic->range()->start());
        }, iterator_to_array($diagnostics)));
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
            $s = $this->reflector->sourceCodeForFunction(
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
