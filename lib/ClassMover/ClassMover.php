<?php

namespace Phpactor\ClassMover;

use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\ClassMover\Domain\ClassFinder;
use Phpactor\ClassMover\Domain\ClassReplacer;
use Phpactor\ClassMover\Adapter\TolerantParser\TolerantClassFinder;
use Phpactor\ClassMover\Adapter\TolerantParser\TolerantClassReplacer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\TolerantUpdater;
use Phpactor\CodeBuilder\Adapter\Twig\TwigRenderer;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextEdits;

class ClassMover
{
    /**
     * @var ClassFinder
     */
    private $finder;

    /**
     * @var ClassReplacer
     */
    private $replacer;

    public function __construct(ClassFinder $finder = null, ClassReplacer $replacer = null)
    {
        $this->finder = $finder ?: new TolerantClassFinder();
        $this->replacer = $replacer ?: new TolerantClassReplacer(new TolerantUpdater(new TwigRenderer()));
    }

    public function findReferences(string $source, string $fullyQualifiedName): FoundReferences
    {
        $source = TextDocumentBuilder::create($source)->build();
        $name = FullyQualifiedName::fromString($fullyQualifiedName);
        $references = $this->finder->findIn($source)->filterForName($name);

        return new FoundReferences($source, $name, $references);
    }

    public function replaceReferences(FoundReferences $foundReferences, string $newFullyQualifiedName): TextEdits
    {
        $newName = FullyQualifiedName::fromString($newFullyQualifiedName);
        return $this->replacer->replaceReferences(
            $foundReferences->source(),
            $foundReferences->references(),
            $foundReferences->targetName(),
            $newName
        );
    }
}
