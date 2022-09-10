<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

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

    /**
     * @return array<string,string>
     */
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
            $map = $this->buildConstantMap($file, $map);
        }

        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }

        file_put_contents($this->serializedMapPath(), serialize($map));
    }

    private function serializedMapPath(): string
    {
        return $this->cacheDir . '/' . md5($this->stubPath) . '.map';
    }

    /**
     * @return RecursiveIteratorIterator<RecursiveDirectoryIterator>
     */
    private function fileIterator(): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->stubPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }

    /**
     * @return array<string,string>
     * @param array<string,string> $map
     */
    private function buildClassMap(SplFileInfo $file, array $map): array
    {
        $functions = $this->reflector->reflectClassesIn(
            SourceCode::fromPath($file)
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
            SourceCode::fromPath($file)
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
    private function buildConstantMap(SplFileInfo $file, array $map): array
    {
        $constants = $this->reflector->reflectConstantsIn(
            SourceCode::fromPath($file)
        );

        foreach ($constants as $constant) {
            $map[(string) $constant->name()] = (string) $file;
        }

        return $map;
    }
}
