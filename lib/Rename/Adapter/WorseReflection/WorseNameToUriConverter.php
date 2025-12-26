<?php

namespace Phpactor\Rename\Adapter\WorseReflection;

use Phpactor\Rename\Model\Exception\CouldNotConvertClassToUri;
use Phpactor\Rename\Model\NameToUriConverter;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Reflector;

class WorseNameToUriConverter implements NameToUriConverter
{
    public function __construct(private readonly Reflector $reflector)
    {
    }


    public function convert(string $className): TextDocumentUri
    {
        try {
            $uri = $this->reflector->reflectClassLike($className)->sourceCode()->uri();
        } catch (NotFound $notFound) {
            throw new CouldNotConvertClassToUri($notFound->getMessage(), 0, $notFound);
        }

        if (null === $uri) {
            throw new CouldNotConvertClassToUri(sprintf('Reflected source for "%s" did not have a URI associated with it', $className));
        }

        return $uri;
    }
}
