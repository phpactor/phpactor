<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Generator;
use Phpactor\CodeTransform\Domain\NameWithByteOffset;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\CandidateFinder;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\NameCandidate;
use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use function Amp\call;

class ImportAllUnresolvedNamesCommand implements Command
{
    public const NAME = 'import_all_unresolved_names';

    public function __construct(
        private CandidateFinder $candidateFinder,
        private Workspace $workspace,
        private ImportNameCommand $importName,
        private ClientApi $client
    ) {
    }

    /**
     * @return Promise<void>
     */
    public function __invoke(
        string $uri
    ): Promise {
        return call(function () use ($uri) {
            $item = $this->workspace->get($uri);
            foreach ((yield $this->candidateFinder->unresolved($item))->onlyUniqueNames() as $unresolvedName) {
                assert($unresolvedName instanceof NameWithByteOffset);
                $candidates = $this->candidates($this->candidateFinder->candidatesForUnresolvedName($unresolvedName));
                $candidate = yield $this->resolveCandidate($unresolvedName, $candidates);
                if (null === $candidate) {
                    $this->client->window()->showMessage()->warning(sprintf(
                        'Class "%s" has no candidates',
                        $unresolvedName->name()->__toString()
                    ));
                    continue;
                }

                yield $this->importName->__invoke(
                    $uri,
                    $unresolvedName->byteOffset()->toInt(),
                    $unresolvedName->type(),
                    $candidate->candidateFqn()
                );
            }
        });
    }

    /**
     * @return Promise<?NameCandidate>
     */
    private function resolveCandidate(NameWithByteOffset $unresolved, array $candidates): Promise
    {
        return call(function () use ($unresolved, $candidates) {
            foreach ($candidates as $candidate) {
                if (count($candidates) === 1) {
                    return $candidate;
                }
                break;
            }

            if (count($candidates) === 0) {
                return null;
            }

            $choice = yield $this->client->window()->showMessageRequest()->info(sprintf(
                'Ambiguous class "%s":',
                $unresolved->name()->__toString()
            ), ...array_map(function (NameCandidate $candidate) {
                return new MessageActionItem($candidate->candidateFqn());
            }, $candidates));

            foreach ($candidates as $candidate) {
                if ($candidate->candidateFqn() === $choice->title) {
                    return $candidate;
                }
            }

            return null;
        });
    }

    /**
     * @param Generator<NameCandidate> $candidates
     */
    private function candidates(Generator $candidates): array
    {
        $filtered = [];
        foreach ($candidates as $candidate) {
            assert($candidate instanceof NameCandidate);
            $filtered[$candidate->candidateFqn()] = $candidate;
        }

        return array_values($filtered);
    }
}
