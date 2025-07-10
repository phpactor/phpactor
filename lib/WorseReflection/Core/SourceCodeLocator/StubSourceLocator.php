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
use RuntimeException;
use SplFileInfo;

final class StubSourceLocator implements SourceCodeLocator
{
    /**
     * @var array<string,string>
     */
    private ?array $map = null;

    public function __construct(
        private Reflector $reflector,
        private string $stubPath,
        private string $cacheDir
    ) {
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
            $this->stubPath
        ));
    }

    /**
     * @return array<string,string>
     */
    private function map(): array
    {
        if ($this->map !== null) {
            return $this->map;
        }

        if (file_exists($this->serializedMapPath())) {
            $map = unserialize((string)file_get_contents($this->serializedMapPath()));

            if (!is_array($map)) {
                throw new RuntimeException(sprintf('Invalid serialized stub data, expected an array, got: %s', get_debug_type($map)));
            }

            /** @var array<string,string> $map */
            $this->map = $map;

            return $this->map;
        }

        $this->buildCache();

        return $this->map();
    }

    private function buildCache(): void
    {
        $map = [];
        foreach ($this->fileIterator() as $file) {
            /** @var SplFileInfo $file */
            if ($file->getExtension() !== 'php' || $file->isDir()) {
                continue;
            }

            $map = $this->buildClassMap($file, $map);
            $map = $this->buildFunctionMap($file, $map);
            $map = $this->buildConstantMap($file, $map);
        }

        if (!file_exists($this->cacheDir)) {
            if (!@mkdir($this->cacheDir, 0777, true)) {
                throw new RuntimeException(sprintf(
                    'Could not create cache dir "%s"',
                    $this->cacheDir
                ));
            }
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

    /**
     * @param array<string,string> $map
     * @return array<string,string>
     */
    private function buildConstantMap(SplFileInfo $file, array $map): array
    {
        $constants = $this->reflector->reflectConstantsIn(
            TextDocumentBuilder::fromUri($file)->build()
        );

        foreach ($constants as $constant) {
            $map[(string) $constant->name()] = (string) $file;
        }

        return $map;
    }
}
