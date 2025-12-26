<?php

namespace Phpactor\Rename\Adapter\ClassToFile;

use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\Rename\Model\Exception\CouldNotConvertUriToClass;
use Phpactor\Rename\Model\UriToNameConverter;
use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;

class ClassToFileUriToNameConverter implements UriToNameConverter
{
    public function __construct(private readonly FileToClass $fileToClass)
    {
    }

    public function convert(TextDocumentUri $uri): string
    {
        try {
            return $this->fileToClass->fileToClassCandidates(FilePath::fromString($uri->path()))->best()->__toString();
        } catch (RuntimeException $error) {
            throw new CouldNotConvertUriToClass($error->getMessage(), 0, $error);
        }
    }
}
