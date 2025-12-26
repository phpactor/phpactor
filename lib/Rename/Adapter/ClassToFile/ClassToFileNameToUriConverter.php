<?php

namespace Phpactor\Rename\Adapter\ClassToFile;

use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\ClassToFile;
use Phpactor\Rename\Model\Exception\CouldNotConvertClassToUri;
use Phpactor\Rename\Model\NameToUriConverter;
use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;

class ClassToFileNameToUriConverter implements NameToUriConverter
{
    public function __construct(private readonly ClassToFile $classToFile)
    {
    }


    public function convert(string $className): TextDocumentUri
    {
        try {
            return TextDocumentUri::fromString($this->classToFile->classToFileCandidates(ClassName::fromString($className))->best());
        } catch (RuntimeException $error) {
            throw new CouldNotConvertClassToUri($error->getMessage(), 0, $error);
        }
    }
}
