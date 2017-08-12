<?php

namespace Phpactor\Application;

use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
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
use Phpactor\ClassMover\Domain\RefReplacer;
use Phpactor\ClassMover\Domain\NamespacedClassRefList;
use Phpactor\ClassMover\Domain\FullyQualifiedName;

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

    /**
     * @var RefReplacer
     */
    private $refReplacer;

    public function __construct(
        ClassFileNormalizer $classFileNormalizer,
        RefFinder $refFinder,
        RefReplacer $refReplacer,
        Filesystem $filesystem
    ) {
        $this->classFileNormalizer = $classFileNormalizer;
        $this->filesystem = $filesystem;
        $this->refFinder = $refFinder;
        $this->refReplacer = $refReplacer;
    }

    public function replaceReferences(string $class, string $replace, bool $dryRun)
    {
        return $this->findReplaceReferences($class, $replace, $dryRun);
    }

    public function findReferences(string $class)
    {
        return $this->findReplaceReferences($class);
    }

    private function findReplaceReferences(string $class, string $replace = null, bool $dryRun = false)
    {
        $classPath = $this->classFileNormalizer->normalizeToFile($class);
        $classPath = Phpactor::normalizePath($classPath);
        $className = $this->classFileNormalizer->normalizeToClass($class);

        $results = [];
        foreach ($this->filesystem->fileList()->phpFiles() as $filePath) {

            $references = [];
            $code = $this->filesystem->getContents($filePath);

            $referenceList = $this->refFinder->findIn(SourceCode::fromString($code), $className);

            if ($replace) {
                $updatedSource = $this->replaceReferencesInCode($code, $referenceList, $class, $replace);

                if (false === $dryRun) {
                    file_put_contents($filePath, (string) $updatedSource);
                }
            }

            /** @var $reference ClassRef */
            foreach ($referenceList as $reference) {
                if ((string) $reference->fullName() != (string) $className) {
                    continue;
                }

                list($lineNumber, $line) = $this->line($code, $reference->position()->start());
                $ref = [
                    'start' => $reference->position()->start(),
                    'end' => $reference->position()->end(),
                    'new' => null,
                    'line' => $line,
                    'line_no' => $lineNumber,
                    'reference' => (string) $reference->name()
                ];

                if ($replace) {
                    $ref['new'] = $this->line((string) $updatedSource, $reference->position()->start());
                }

                $references[] = $ref;
            }


            if (count($references)) {
                $results[] = [
                    'file' => (string) $filePath,
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
        $number = 0;
        $startPosition = 0;

        foreach ($lines as $number => $line) {
            $number = $number + 1;
            $endPosition = $startPosition + strlen($line) + 1;

            if ($offset >= $startPosition && $offset <= $endPosition) {
                return [ $number, $line ];
            }

            $startPosition = $endPosition;
        }

        return [$number, ''];
    }

    private function replaceReferencesInCode(string $code, NamespacedClassRefList $list, string $class, string $replace): SourceCode
    {
        $class = FullyQualifiedName::fromString($class);
        $replace = FullyQualifiedName::fromString($replace);
        $code = SourceCode::fromString($code);

        return $this->refReplacer->replaceReferences($code, $list, $class, $replace);
    }
}

