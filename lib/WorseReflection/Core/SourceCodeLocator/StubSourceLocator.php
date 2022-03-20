<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class StubSourceLocator implements SourceCodeLocator
{
    private string $cacheDir;
    
    private Reflector $reflector;
    
    private string $stubPath;

    public function __construct(Reflector $reflector, string $stubPath, string $cacheDir)
    {
        $this->reflector = $reflector;
        $this->stubPath = $stubPath;
        $this->cacheDir = $cacheDir;
    }

    public function locate(Name $name): SourceCode
    {
        $map = $this->map();

        if (isset($map[(string) $name])) {
            return SourceCode::fromPath($map[(string) $name]);
        }

        throw new SourceNotFound(sprintf(
            'Could not find source for "%s" in stub directory "%s"',
            (string) $name,
            $this->stubPath
        ));
    }

    private function map(): array
    {
        if (file_exists($this->serializedMapPath())) {
            return unserialize((string)file_get_contents($this->serializedMapPath()));
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

        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }

        file_put_contents($this->serializedMapPath(), serialize($map));
    }

    private function serializedMapPath()
    {
        return $this->cacheDir . '/' . md5($this->stubPath) . '.map';
    }

    private function fileIterator()
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->stubPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }

    private function buildClassMap($file, array $map): array
    {
        $functions = $this->reflector->reflectClassesIn(
            SourceCode::fromPath($file)
        );
        
        foreach ($functions as $function) {
            $map[(string) $function->name()] = (string) $file;
        }

        return $map;
    }

    private function buildFunctionMap($file, array $map): array
    {
        $functions = $this->reflector->reflectFunctionsIn(
            SourceCode::fromPath($file)
        );
        
        foreach ($functions as $function) {
            $map[(string) $function->name()] = (string) $file;
        }

        return $map;
    }
}
