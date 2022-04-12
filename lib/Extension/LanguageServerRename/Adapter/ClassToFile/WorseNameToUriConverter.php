<?php

namespace Phpactor\Extension\LanguageServerRename\Adapter\ClassToFile;

use Phpactor\Extension\LanguageServerRename\Model\Exception\CouldNotConvertClassToUri;
use Phpactor\Extension\LanguageServerRename\Model\NameToUriConverter;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Reflector;

class WorseNameToUriConverter implements NameToUriConverter
{
    private Reflector $reflector;

    public function __construct(
        Reflector $reflector
    ) {
        $this->reflector = $reflector;
    }

    /**
     * {@inheritDoc}
     */
    public function convert(string $className): TextDocumentUri
    {
        $uri = $this->reflector->reflectClass($className)->sourceCode()->uri();

        if (null === $uri) {
            throw new CouldNotConvertClassToUri();
        }

        return $uri;
    }
}
