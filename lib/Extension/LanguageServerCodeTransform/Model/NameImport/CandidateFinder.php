<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport;

use Amp\Promise;
use Generator;
use Phpactor\CodeTransform\Domain\NameWithByteOffsets;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\ConstantRecord;
use Phpactor\Indexer\Model\Record\HasFullyQualifiedName;
use Phpactor\Indexer\Model\SearchClient;
use Phpactor\CodeTransform\Domain\NameWithByteOffset;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnresolvableNameDiagnostic;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Reflector;
use function Amp\call;

class CandidateFinder
{
    public function __construct(private Reflector $reflector, private SearchClient $client)
    {
    }

    /**
     * @return Promise<NameWithByteOffsets>
     */
    public function unresolved(TextDocumentItem $item): Promise
    {
        return call(function () use ($item) {
            $diagnostics = (yield $this->reflector->diagnostics(TextDocumentConverter::fromLspTextItem($item)))->byClass(UnresolvableNameDiagnostic::class);

            return new NameWithByteOffsets(...array_map(function (UnresolvableNameDiagnostic $diagnostic): NameWithByteOffset {
                return new NameWithByteOffset($diagnostic->name(), $diagnostic->range()->start(), $diagnostic->type());
            }, iterator_to_array($diagnostics)));
        });
    }

    /**
     * @return Promise<list<NameCandidate>>
     */
    public function importCandidates(
        TextDocumentItem $item
    ): Promise {
        return call(function () use ($item) {
            $candidates = [];
            $seen = [];
            foreach (yield $this->unresolved($item) as $unresolvedName) {
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
                    $candidates[] = $candidate;
                }
            }
            return $candidates;
        });
    }

    /**
     * @return Generator<NameCandidate>
     */
    public function candidatesForUnresolvedName(NameWithByteOffset $unresolvedName): Generator
    {
        if ($this->isUnresolvedGlobalFunction($unresolvedName)) {
            yield new NameCandidate($unresolvedName, $unresolvedName->name()->head()->__toString());
            return;
        }
        assert($unresolvedName instanceof NameWithByteOffset);

        foreach ($this->findCandidates($unresolvedName) as $candidate) {
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
        } catch (NotFound) {
        }

        return false;
    }

    /**
     * @return Generator<Record>
     */
    private function findCandidates(NameWithByteOffset $unresolvedName): Generator
    {
        yield from $this->client->search(Criteria::and(
            Criteria::or(
                Criteria::isConstant(),
                Criteria::isClass(),
                Criteria::isFunction()
            ),
            Criteria::exactShortName($unresolvedName->name()->head()->__toString())
        ));
    }
}
