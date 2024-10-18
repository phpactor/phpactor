<?php

namespace Phpactor\Completion\Bridge\TolerantParser\SourceCodeFilesystem;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\ResolvedName;
use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\ClassQualifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifiable;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;
use Phpactor\Completion\Core\Range;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Filesystem\Domain\FilePath as ScfFilePath;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use SplFileInfo;

class ScfClassCompletor implements TolerantCompletor, TolerantQualifiable
{
    private ClassQualifier $qualifier;

    public function __construct(
        private Filesystem $filesystem,
        private FileToClass $fileToClass,
        ?ClassQualifier $qualifier = null
    ) {
        $this->qualifier = $qualifier ?: new ClassQualifier();
    }

    public function qualifier(): TolerantQualifier
    {
        return $this->qualifier;
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $files = $this->filesystem->fileList()->phpFiles();

        if ($node instanceof QualifiedName) {
            $files = $files->filter(function (SplFileInfo $file) use ($node) {
                return str_starts_with($file->getFilename(), $node->getText());
            });
        }

        $count = 0;
        $currentNamespace = $this->getCurrentNamespace($node);
        $imports = $node->getImportTablesForCurrentScope();

        /** @var ScfFilePath $file */
        foreach ($files as $file) {
            $candidates = $this->fileToClass->fileToClassCandidates(FilePath::fromString($file->path()));

            if ($candidates->noneFound()) {
                continue;
            }

            foreach ($candidates as $candidate) {
                yield Suggestion::createWithOptions(
                    $candidate->name(),
                    [
                        'type' => Suggestion::TYPE_CLASS,
                        'short_description' => $candidate->__toString(),
                        'class_import' => $this->getClassNameForImport($candidate, $imports, $currentNamespace),
                        'range' => $this->getRange($node, $offset),
                    ]
                );
            }
        }

        return true;
    }
    /**
     * @param array<int,ResolvedName[]> $imports
     */
    private function getClassNameForImport(ClassName $candidate, array $imports, ?string $currentNamespace = null): ?string
    {
        $candidateNamespace = $candidate->namespace();

        if ((string) $currentNamespace === (string) $candidateNamespace) {
            return null;
        }

        foreach ($imports[0] as $resolvedName) {
            if ($candidate->__toString() === $resolvedName->getFullyQualifiedNameText()) {
                return null;
            }
        }

        return $candidate->__toString();
    }


    private function getCurrentNamespace(Node $node): ?string
    {
        $currentNamespaceDefinition = $node->getNamespaceDefinition();

        if (!$currentNamespaceDefinition) {
            return null;
        }

        if (!$currentNamespaceDefinition->name instanceof QualifiedName) {
            return null;
        }

        return $currentNamespaceDefinition->name->getText();
    }

    private function getRange(Node $node, ByteOffset $offset): Range
    {
        if ($node instanceof QualifiedName) {
            return Range::fromStartAndEnd($node->getStartPosition(), $node->getEndPosition());
        }

        return new Range($offset, $offset);
    }
}
