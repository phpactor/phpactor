<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor;

use Phpactor\ClassFileConverter\Domain\ClassToFile;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\TextDocument\TextDocument;
use Phpactor\ClassFileConverter\Domain\ClassName;

class ClassToFileSourceLocator implements SourceCodeLocator
{
    public function __construct(private ClassToFile $converter)
    {
    }

    public function locate(Name $name): TextDocument
    {
        $candidates = $this->converter->classToFileCandidates(ClassName::fromString((string) $name));

        if ($candidates->noneFound()) {
            throw new SourceNotFound(sprintf('Could not locate a candidate for "%s"', (string) $name));
        }

        foreach ($candidates as $candidate) {
            if (file_exists((string) $candidate)) {
                return TextDocument::fromPath((string) $candidate);
            }
        }

        throw new SourceNotFound($name);
    }
}
