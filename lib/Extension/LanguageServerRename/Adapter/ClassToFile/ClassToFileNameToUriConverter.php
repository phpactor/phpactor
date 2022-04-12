<?php

namespace Phpactor\Extension\LanguageServerRename\Adapter\ClassToFile;

use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\ClassToFile;
use Phpactor\Extension\LanguageServerRename\Model\Exception\CouldNotConvertClassToUri;
use Phpactor\Extension\LanguageServerRename\Model\NameToUriConverter;
use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;

class ClassToFileNameToUriConverter implements NameToUriConverter
{
    private ClassToFile $classToFile;

    public function __construct(
        ClassToFile $classToFile
    ) {
        $this->classToFile = $classToFile;
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
