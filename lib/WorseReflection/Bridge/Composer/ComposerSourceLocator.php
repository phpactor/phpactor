<?php

namespace Phpactor\WorseReflection\Bridge\Composer;

use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Composer\Autoload\ClassLoader;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;

class ComposerSourceLocator implements SourceCodeLocator
{
    public function __construct(private readonly ClassLoader $classLoader)
    {
    }

    public function locate(Name $className): TextDocument
    {
        $path = $this->classLoader->findFile((string) $className);

        if (false === $path) {
            throw new SourceNotFound(sprintf(
                'Composer could not locate file for class "%s"',
                $className->full()
            ));
        }

        return TextDocumentBuilder::fromUri($path)->build();
    }
}
