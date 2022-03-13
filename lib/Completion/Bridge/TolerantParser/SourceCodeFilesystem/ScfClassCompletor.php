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
    private Filesystem $filesystem;

    private FileToClass $fileToClass;

    private ClassQualifier $qualifier;

    public function __construct(Filesystem $filesystem, FileToClass $fileToClass, ?ClassQualifier $qualifier = null)
    {
        $this->filesystem = $filesystem;
        $this->fileToClass = $fileToClass;
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
                return 0 === strpos($file->getFilename(), $node->getText());
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

    private function getClassNameForImport(ClassName $candidate, array $imports, string $currentNamespace = null): ?string
    {
        $candidateNamespace = $candidate->namespace();

        if ((string) $currentNamespace === (string) $candidateNamespace) {
            return null;
        }

        /** @var ResolvedName $resolvedName */
        foreach ($imports[0] as $resolvedName) {
            if ($candidate->__toString() === $resolvedName->getFullyQualifiedNameText()) {
                return null;
            }
        }

        return $candidate->__toString();
    }

    /**
     * @return string|null
     */
    private function getCurrentNamespace(Node $node)
    {
        $currentNamespaceDefinition = $node->getNamespaceDefinition();

        return null !== $currentNamespaceDefinition && null !== $currentNamespaceDefinition->name
            ? $currentNamespaceDefinition->name->getText()
            : null;
    }

    private function getRange(Node $node, ByteOffset $offset): Range
    {
        if ($node instanceof QualifiedName) {
            return Range::fromStartAndEnd($node->getStartPosition(), $node->getEndPosition());
        }

        return new Range($offset, $offset);
    }
}
