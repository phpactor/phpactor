<?php

namespace Phpactor\Rename\Adapter\ClassMover;

use Amp\Promise;
use Generator;
use Phpactor\ClassMover\ClassMover;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Rename\Model\Exception\CouldNotConvertUriToClass;
use Phpactor\Rename\Model\Exception\CouldNotRename;
use Phpactor\Rename\Model\FileRenamer as PhpactorFileRenamer;
use Phpactor\Rename\Model\LocatedTextEdit;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\Rename\Model\WorkspaceRenameEdits;
use Phpactor\Rename\Model\RenameResult;
use Phpactor\Rename\Model\UriToNameConverter;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use function Amp\call;

class FileRenamer implements PhpactorFileRenamer
{
    public function __construct(
        private UriToNameConverter $converter,
        private TextDocumentLocator $locator,
        private QueryClient $client,
        private ClassMover $mover,
    ) {
    }

    public function renameFile(TextDocumentUri $from, TextDocumentUri $to): Promise
    {
        return call(function () use ($from, $to) {
            try {
                $fromClass = $this->converter->convert($from);
                $toClass = $this->converter->convert($to);
            } catch (CouldNotConvertUriToClass $error) {
                throw new CouldNotRename($error->getMessage(), 0, $error);
            }

            $references = $this->client->class()->referencesTo($fromClass);

            // rename class definition
            $locatedEdits = [...$this->replaceDefinition($from, $fromClass, $toClass)];

            $seen = [];
            foreach ($references as $reference) {
                if (isset($seen[$reference->location()->uri()->__toString()])) {
                    continue;
                }

                $seen[$reference->location()->uri()->__toString()] = true;

                try {
                    $document = $this->locator->get($reference->location()->uri());
                } catch (TextDocumentNotFound) {
                    continue;
                }

                foreach ($this->mover->replaceReferences(
                    $this->mover->findReferences($document->__toString(), $fromClass),
                    $toClass
                ) as $edit) {
                    $locatedEdits[] = new LocatedTextEdit($reference->location()->uri(), $edit);
                }
            }

            return new WorkspaceRenameEdits([
                LocatedTextEditsMap::fromLocatedEdits($locatedEdits),
                new RenameResult($from, $to),
            ]);
        });
    }

    /**
     * @return Generator<LocatedTextEdit>
     */
    private function replaceDefinition(TextDocumentUri $file, string $fromClass, string $toClass): Generator
    {
        $document = $this->locator->get($file);
        foreach ($this->mover->replaceReferences(
            $this->mover->findReferences($document, $fromClass),
            $toClass
        ) as $edit) {
            yield new LocatedTextEdit($file, $edit);
        }
    }
}
