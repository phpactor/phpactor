<?php

namespace Phpactor\Rename\Adapter\Test;

use Phpactor\Rename\Model\NameToUriConverter;
use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;

final class TestNameToUriConverter implements NameToUriConverter
{
    /**
     * @param array<string,TextDocumentUri> $map
     */
    public function __construct(private array $map)
    {
    }

    public function convert(string $className): TextDocumentUri
    {
        if (!isset($this->map[$className])) {
            throw new RuntimeException(sprintf(
                'Test class name "%s" not mapped to file',
                $className
            ));
        }

        return $this->map[$className];
    }

}
