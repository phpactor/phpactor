<?php

namespace Phpactor\Rename\Adapter\ClassMover;

use Amp\Promise;
use Phpactor\ClassMover\ClassMover;
use Phpactor\Rename\Model\Exception\CouldNotConvertUriToClass;
use Phpactor\Rename\Model\Exception\CouldNotRename;
use Phpactor\Rename\Model\FileRenamer as PhpactorFileRenamer;
use Phpactor\Rename\Model\LocatedTextEdit;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\Rename\Model\UriToNameConverter;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdits;
use function Amp\call;

class FileRenamer implements PhpactorFileRenamer
{
    private QueryClient $client;

    private ClassMover $mover;

    private TextDocumentLocator $locator;

    private UriToNameConverter $converter;

    public function __construct(
        UriToNameConverter $converter,
        TextDocumentLocator $locator,
        QueryClient $client,
        ClassMover $mover
    ) {
        $this->client = $client;
        $this->mover = $mover;
        $this->locator = $locator;
        $this->converter = $converter;
    }

    /**
     * @return Promise<LocatedTextEditsMap>
     */
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
            $locatedEdits = $this->replaceDefinition($to, $fromClass, $toClass);

            $edits = TextEdits::none();
            $seen = [];
            foreach ($references as $reference) {
                if (isset($seen[$reference->location()->uri()->path()])) {
                    continue;
                }

                $seen[$reference->location()->uri()->path()] = true;

                try {
                    $document = $this->locator->get($reference->location()->uri());
                } catch (TextDocumentNotFound $notFound) {
                    continue;
                }

                foreach ($this->mover->replaceReferences(
                    $this->mover->findReferences($document->__toString(), $fromClass),
                    $toClass
                ) as $edit) {
                    $locatedEdits[] = new LocatedTextEdit($reference->location()->uri(), $edit);
                }
            }

            return LocatedTextEditsMap::fromLocatedEdits($locatedEdits);
        });
    }

    /**
     * @return LocatedTextEdit[]
     */
    private function replaceDefinition(TextDocumentUri $file, string $fromClass, string $toClass): array
    {
        $document = $this->locator->get($file);
        $locatedEdits = [];
        foreach ($this->mover->replaceReferences(
            $this->mover->findReferences($document, $fromClass),
            $toClass
        ) as $edit) {
            $locatedEdits[] = new LocatedTextEdit($file, $edit);
        }

        return $locatedEdits;
    }
}
