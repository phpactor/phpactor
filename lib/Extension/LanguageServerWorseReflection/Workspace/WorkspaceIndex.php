<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Workspace;

use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use RuntimeException;
use function Amp\asyncCall;
use function Amp\delay;

class WorkspaceIndex
{
    /**
     * @var array<string, TextDocument>
     */
    private $byName = [];
    /**
     * @var array<string, TextDocument>
     */
    private $documents = [];
    /**
     * @var array<string, array<string>>
     */
    private $documentToNameMap = [];

    /**
     * @var TextDocument|null
     */
    private $documentToUpdate;

    /**
     * @var bool
     */
    private $waiting = false;

    public function __construct(
        private SourceCodeReflector $reflector,
        private int $updateInterval = 1000
    ) {
    }

    public function documentForName(Name $name): ?TextDocument
    {
        return $this->byName[$name->full()] ?? null;
    }

    public function index(TextDocument $textDocument): void
    {
        $this->documents[(string)$textDocument->uri()] = $textDocument;
        $this->updateDocument($textDocument);
    }

    /**
     * Refresh the document.
     *
     * In order to prevent continuous reparsing the document will
     * only be refreshed at the sepecified interval.
     */
    private function updateDocument(TextDocument $textDocument): void
    {
        if ($this->waiting) {
            $this->documentToUpdate = $textDocument;
            return;
        }

        $this->documentToUpdate = null;

        $newNames = [];
        foreach ($this->reflector->reflectClassLikesIn($textDocument) as $reflectionClass) {
            $newNames[] = $reflectionClass->name()->full();
        }

        foreach ($this->reflector->reflectFunctionsIn($textDocument) as $reflectionFunction) {
            $newNames[] = $reflectionFunction->name()->full();
        }

        $this->updateNames(
            $textDocument,
            $newNames,
            $this->documentToNameMap[(string)$textDocument->uri()] ?? []
        );

        if ($this->updateInterval === 0) {
            return;
        }

        $this->waiting = true;

        asyncCall(function () {
            yield delay($this->updateInterval);

            $this->waiting = false;

            if (null === $this->documentToUpdate) {
                return;
            }

            $this->updateDocument($this->documentToUpdate);
        });
    }

    private function updateNames(TextDocument $textDocument, array $newNames, array $currentNames): void
    {
        $namesToRemove = array_diff($currentNames, $newNames);

        foreach ($newNames as $name) {
            $this->byName[(string)$name] = $textDocument;
        }
        foreach ($namesToRemove as $name) {
            unset($this->byName[$name]);
        }

        if ($newNames !== []) {
            $this->documentToNameMap[(string)$textDocument->uri()] = $newNames;
        } else {
            unset($this->documentToNameMap[(string)$textDocument->uri()]);
        }
    }

    public function update(TextDocumentUri $textDocumentUri, string $updatedText): void
    {
        $textDocument = $this->documents[(string)$textDocumentUri] ?? null;
        if ($textDocument === null) {
            throw new RuntimeException(sprintf(
                'Could not find document "%s"',
                $textDocumentUri->__toString()
            ));
        }
        $this->updateDocument(TextDocumentBuilder::fromTextDocument($textDocument)->text($updatedText)->build());
    }

    public function remove(TextDocumentUri $textDocumentUri): void
    {
        $textDocument = $this->documents[(string)$textDocumentUri] ?? null;
        if ($textDocument === null) {
            throw new RuntimeException(sprintf(
                'Could not find document "%s"',
                $textDocumentUri->__toString()
            ));
        }
        $this->updateNames($textDocument, [], $this->documentToNameMap[(string)$textDocument->uri()] ?? []);
        unset($this->documents[(string)$textDocumentUri]);
    }
}
