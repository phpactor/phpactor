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
use Phpactor\ClassMover\Domain\MethodFinder;
use Phpactor\ClassMover\Domain\ClassRef;
use Phpactor\ClassMover\Domain\ClassReplacer;
use Phpactor\ClassMover\Domain\NamespacedClassRefList;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\ClassMover\Domain\Reference\NamespacedClassReferences;
use Phpactor\ClassMover\Domain\Reference\ClassReference;
use Phpactor\ClassMover\Domain\Model\Class_;
use Phpactor\ClassMover\Domain\Model\ClassMethodQuery;
use Phpactor\ClassMover\Domain\Name\MethodName;
use Phpactor\ClassMover\Domain\Reference\MethodReferences;
use Phpactor\ClassMover\Domain\Reference\MethodReference;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\ClassName;
use \SplFileInfo;

class ClassMethodReferences
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var MethodFinder
     */
    private $methodFinder;

    /**
     * @var ClassFileNormalizer
     */
    private $classFileNormalizer;

    /**
     * @var Reflector
     */
    private $reflector;

    public function __construct(
        ClassFileNormalizer $classFileNormalizer,
        MethodFinder $methodFinder,
        Filesystem $filesystem,
        Reflector $reflector
    ) {
        $this->classFileNormalizer = $classFileNormalizer;
        $this->filesystem = $filesystem;
        $this->methodFinder = $methodFinder;
        $this->reflector = $reflector;
    }

    public function findReferences(string $class = null, string $methodName = null)
    {
        $className = $class ? $this->classFileNormalizer->normalizeToClass($class) : null;

        $results = [];
        $filePaths = $this->filesystem->fileList()->phpFiles();

        // we can discount any files that do not contain the method name.
        if ($methodName) {
            $filePaths = $filePaths->filter(function (SplFileInfo $file) use ($methodName) {
                return preg_match('{' . $methodName . '}', file_get_contents($file->getPathname()));
            });
        }

        foreach ($filePaths as $filePath) {

            $references = $this->referencesInFile($filePath, $className, $methodName);

            if (empty($references['references'])) {
                continue;
            }

            $references['file'] = (string) $filePath;
            $results[] = $references;
        }

        if ($methodName && $className && empty($results)) {
            $reflection = $this->reflector->reflectClass(ClassName::fromString($className));

            if (false === $reflection->methods()->has($methodName)) {
                throw new \InvalidArgumentException(sprintf(
                    'Method not known "%s", known methods: "%s"',
                    $methodName, implode('", "', $reflection->methods()->keys())
                ));
            }
        }

        return [
            'references' => $results
        ];
    }

    private function referencesInFile($filePath, string $className = null, string $methodName = null)
    {
        $code = $this->filesystem->getContents($filePath);

        $query = $this->createQuery($className, $methodName);

        $referenceList = $this->methodFinder->findMethods(
            SourceCode::fromString($code),
            $query
        );

        $result = [
            'references' => [],
            'replacements' => [],
        ];

        $result['references'] = $this->serializeReferenceList($code, $referenceList);

        return $result;
    }

    private function serializeReferenceList(string $code, MethodReferences $referenceList)
    {
        $references = [];
        /** @var $reference ClassRef */
        foreach ($referenceList as $reference) {
            $ref = $this->serializeReference($code, $reference);

            $references[] = $ref;
        }

        return $references;
    }

    private function serializeReference(string $code, MethodReference $reference)
    {
        list($lineNumber, $colNumber, $line) = $this->line($code, $reference->position()->start());
        return [
            'start' => $reference->position()->start(),
            'end' => $reference->position()->end(),
            'line' => $line,
            'line_no' => $lineNumber,
            'col_no' => $colNumber,
            'reference' => (string) $reference->methodName()
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

    private function createQuery(string $className = null, string $methodName = null)
    {
        if ($className && $methodName) {
            return ClassMethodQuery::fromScalarClassAndMethodName($className, $methodName);
        }

        if ($className) {
            return ClassMethodQuery::fromScalarClass($className);
        }

        return ClassMethodQuery::all();
    }
}
