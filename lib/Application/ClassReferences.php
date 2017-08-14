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
use Phpactor\ClassMover\Domain\ClassFinder;
use Phpactor\ClassMover\Domain\ClassRef;
use Phpactor\ClassMover\Domain\ClassReplacer;
use Phpactor\ClassMover\Domain\NamespacedClassRefList;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\ClassMover\Domain\Reference\NamespacedClassReferences;
use Phpactor\ClassMover\Domain\Reference\ClassReference;

class ClassReferences
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ClassFinder
     */
    private $refFinder;

    /**
     * @var ClassFileNormalizer
     */
    private $classFileNormalizer;

    /**
     * @var ClassReplacer
     */
    private $refReplacer;

    public function __construct(
        ClassFileNormalizer $classFileNormalizer,
        ClassFinder $refFinder,
        ClassReplacer $refReplacer,
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

            $references = $this->fileReferences($filePath, $className, $replace, $dryRun);

            if (empty($references['references'])) {
                continue;
            }

            $references['file'] = (string) $filePath;
            $results[] = $references;
        }

        return [
            'references' => $results
        ];
    }

    private function fileReferences($filePath, $className, $replace = null, $dryRun = false)
    {
        $code = $this->filesystem->getContents($filePath);

        $referenceList = $this->refFinder
            ->findIn(SourceCode::fromString($code))
            ->filterForName(FullyQualifiedName::fromString($className));

        $result = [
            'references' => [],
            'replacements' => [],
        ];

        if ($referenceList->isEmpty()) {
            return $result;
        }

        if ($replace) {
            $updatedSource = $this->replaceReferencesInCode($code, $referenceList, $className, $replace);

            if (false === $dryRun) {
                file_put_contents($filePath, (string) $updatedSource);
            }
        }

        $result['references'] = $this->serializeReferenceList($code, $referenceList);

        if ($replace) {
            $newReferenceList = $this->refFinder
                ->findIn(SourceCode::fromString((string) $updatedSource))
                ->filterForName(FullyQualifiedName::fromString($replace));

            $result['replacements'] = $this->serializeReferenceList((string) $updatedSource, $newReferenceList);
        }

        return $result;
    }

    private function serializeReferenceList(string $code, NamespacedClassReferences $referenceList)
    {
        $references = [];
        /** @var $reference ClassRef */
        foreach ($referenceList as $reference) {
            $ref = $this->serializeReference($code, $reference);

            $references[] = $ref;
        }

        return $references;
    }

    private function serializeReference(string $code, ClassReference $reference)
    {
        list($lineNumber, $colNumber, $line) = $this->line($code, $reference->position()->start());
        return [
            'start' => $reference->position()->start(),
            'end' => $reference->position()->end(),
            'line' => $line,
            'line_no' => $lineNumber,
            'col_no' => $colNumber,
            'reference' => (string) $reference->name()
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
                $col = $offset - $startPosition;
                return [ $number, $col, $line ];
            }

            $startPosition = $endPosition;
        }

        return [$number, 0, ''];
    }

    private function replaceReferencesInCode(string $code, NamespacedClassReferences $list, string $class, string $replace): SourceCode
    {
        $class = FullyQualifiedName::fromString($class);
        $replace = FullyQualifiedName::fromString($replace);
        $code = SourceCode::fromString($code);

        return $this->refReplacer->replaceReferences($code, $list, $class, $replace);
    }
}

