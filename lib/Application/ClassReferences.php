<?php

namespace Phpactor\Application;

use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\ClassReferences\ClassReferences as ClassReferencesFacade;
use Phpactor\ClassReferences\Domain\FullyQualifiedName;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Phpactor;
use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;
use Phpactor\Application\Logger\ClassReferencesLogger;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\ClassMover\Domain\SourceCode;
use Phpactor\ClassMover\Domain\RefFinder;
use Phpactor\ClassMover\Domain\ClassRef;

class ClassReferences
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var RefFinder
     */
    private $refFinder;

    /**
     * @var ClassFileNormalizer
     */
    private $classFileNormalizer;

    public function __construct(
        ClassFileNormalizer $classFileNormalizer,
        RefFinder $refFinder,
        Filesystem $filesystem
    ) {
        $this->classFileNormalizer = $classFileNormalizer;
        $this->filesystem = $filesystem;
        $this->refFinder = $refFinder;
    }

    public function findReferences(string $class)
    {
        $classPath = $this->classFileNormalizer->normalizeToFile($class);
        $classPath = Phpactor::normalizePath($classPath);
        $className = $this->classFileNormalizer->normalizeToClass($class);

        $results = [];
        foreach ($this->filesystem->fileList()->phpFiles() as $filePath) {

            $references = [];
            $code = $this->filesystem->getContents($filePath);

            /** @var $reference ClassRef */
            foreach ($this->refFinder->findIn(SourceCode::fromString($code), $className) as $reference) {
                if ((string) $reference->fullName() == (string) $className) {
                    $references[] = [
                        'start' => $reference->position()->start(),
                        'end' => $reference->position()->end(),
                        'line' => $this->line($code, $reference->position()->start())
                    ];
                }
            }


            if (count($references)) {
                $results[] = [
                    'file' => $filePath,
                    'references' => $references,
                ];
            }
        }

        return [
            'references' => $results
        ];
    }

    private function line(string $code, int $offset)
    {
        $lines = explode(PHP_EOL, $code);
        $startPosition = 0;

        foreach ($lines as $line) {
            $endPosition = $startPosition + strlen($line) + 1;

            if ($offset >= $startPosition && $offset <= $endPosition) {
                return trim($line);
            }

            $startPosition = $endPosition;
        }

        return '';

    }
}

