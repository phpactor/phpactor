<?php

namespace Phpactor\Extension\LanguageServerRename\Adapter\ClassToFile;

use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\Extension\LanguageServerRename\Model\Exception\CouldNotConvertUriToClass;
use Phpactor\Extension\LanguageServerRename\Model\UriToNameConverter;
use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;

class ClassToFileUriToNameConverter implements UriToNameConverter
{
    /**
     * @var FileToClass
     */
    private $fileToClass;

    public function __construct(FileToClass $fileToClass)
    {
        $this->fileToClass = $fileToClass;
    }

    /**
     * {@inheritDoc}
     */
    public function convert(TextDocumentUri $uri): string
    {
        try {
            return $this->fileToClass->fileToClassCandidates(FilePath::fromString($uri->path()))->best()->__toString();
        } catch (RuntimeException $error) {
            throw new CouldNotConvertUriToClass($error->getMessage(), 0, $error);
        }
    }
}
