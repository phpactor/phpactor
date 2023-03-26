<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class BruteForceSourceLocator implements SourceCodeLocator
{
    /**
     * @var null|array<string,string>
     */
    private ?array $map = null;

    public function __construct(private Reflector $reflector, private string $path)
    {
    }

    public function locate(Name $name): TextDocument
    {
        $map = $this->map();

        if (isset($map[(string) $name])) {
            return TextDocumentBuilder::fromUri($map[(string) $name])->build();
        }

        throw new SourceNotFound(sprintf(
            'Could not find source for "%s" in stub directory "%s"',
            (string) $name,
            $this->path
        ));
    }

    /**
     * @return array<string,string>
     */
    private function map(): array
    {
        if (null !== $this->map) {
            return $this->map;
        }

        $this->buildCache();
        return $this->map();
    }

    private function buildCache(): void
    {
        $map = [];
        foreach ($this->fileIterator() as $file) {
            if ($file->getExtension() !== 'php' || $file->isDir()) {
                continue;
            }

            $map = $this->buildClassMap($file, $map);
            $map = $this->buildFunctionMap($file, $map);
        }

        $this->map = $map;
    }

    /**
     * @return RecursiveIteratorIterator<RecursiveDirectoryIterator>
     */
    private function fileIterator(): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }

    /**
     * @param array<string,string> $map
     * @return array<string,string>
     */
    private function buildClassMap(SplFileInfo $file, array $map): array
    {
        $functions = $this->reflector->reflectClassesIn(
            TextDocumentBuilder::fromUri($file)->build()
        );

        foreach ($functions as $function) {
            $map[(string) $function->name()] = (string) $file;
        }

        return $map;
    }

    /**
     * @param array<string,string> $map
     * @return array<string,string>
     */
    private function buildFunctionMap(SplFileInfo $file, array $map): array
    {
        $functions = $this->reflector->reflectFunctionsIn(
            TextDocumentBuilder::fromUri($file)->build()
        );

        foreach ($functions as $function) {
            $map[(string) $function->name()] = (string) $file;
        }

        return $map;
    }
}
