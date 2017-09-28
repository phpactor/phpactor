<?php

namespace Phpactor\Application;

use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\ClassMover\Domain\SourceCode;
use Phpactor\ClassMover\Domain\ClassRef;
use Phpactor\ClassMover\Domain\Model\ClassMemberQuery;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\ClassName;
use \SplFileInfo;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\ClassMover\Domain\Reference\MemberReferences;
use Phpactor\ClassMover\Domain\MemberFinder;
use Phpactor\ClassMover\Domain\MemberReplacer;
use Phpactor\ClassMover\Domain\Reference\MemberReference;

class ClassMethodReferences
{
    /**
     * @var FilesystemRegistry
     */
    private $filesystemRegistry;

    /**
     * @var MemberFinder
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

    /**
     * @var MemberReplacer
     */
    private $methodReplacer;

    public function __construct(
        ClassFileNormalizer $classFileNormalizer,
        MemberFinder $methodFinder,
        MemberReplacer $methodReplacer,
        FilesystemRegistry $filesystemRegistry,
        Reflector $reflector
    ) {
        $this->classFileNormalizer = $classFileNormalizer;
        $this->filesystemRegistry = $filesystemRegistry;
        $this->methodFinder = $methodFinder;
        $this->reflector = $reflector;
        $this->methodReplacer = $methodReplacer;
    }

    public function findOrReplaceReferences(string $scope, string $class = null, string $memberName = null, string $replace = null, bool $dryRun = false)
    {
        $className = $class ? $this->classFileNormalizer->normalizeToClass($class) : null;

        $filesystem = $this->filesystemRegistry->get($scope);
        $results = [];
        $filePaths = $filesystem->fileList()->phpFiles();

        // we can discount any files that do not contain the method name.
        if ($memberName) {
            $filePaths = $filePaths->filter(function (SplFileInfo $file) use ($memberName) {
                return preg_match('{' . $memberName . '}', file_get_contents($file->getPathname()));
            });
        }

        foreach ($filePaths as $filePath) {
            $references = $this->referencesInFile($filesystem, $filePath, $className, $memberName, $replace, $dryRun);

            if (empty($references['references']) && empty($references['risky_references'])) {
                continue;
            }

            $references['file'] = (string) $filePath;
            $results[] = $references;
        }

        if ($memberName && $className && empty($results)) {
            $reflection = $this->reflector->reflectClassLike(ClassName::fromString($className));

            if (false === $reflection->methods()->has($memberName)) {
                throw new \InvalidArgumentException(sprintf(
                    'Method not known "%s", known methods: "%s"',
                    $memberName,
                    implode('", "', $reflection->methods()->keys())
                ));
            }
        }

        return [
            'references' => $results
        ];
    }

    private function referencesInFile(Filesystem $filesystem, $filePath, string $className = null, string $memberName = null, string $replace = null, bool $dryRun = false)
    {
        $code = $filesystem->getContents($filePath);

        $query = $this->createQuery($className, $memberName);

        $referenceList = $this->methodFinder->findMembers(
            SourceCode::fromString($code),
            $query
        );
        $confidentList = $referenceList->withClasses();
        $riskyList = $referenceList->withoutClasses();

        $result = [
            'references' => [],
            'risky_references' => [],
            'replacements' => [],
        ];

        $result['references'] = $this->serializeReferenceList($code, $confidentList);
        $result['risky_references'] = $this->serializeReferenceList($code, $riskyList);

        if ($replace) {
            $updatedSource = $this->replaceReferencesInCode($code, $confidentList, $replace);

            if (false === $dryRun) {
                file_put_contents($filePath, (string) $updatedSource);
            }

            $query = $this->createQuery($className, $replace);

            $replacedReferences = $this->methodFinder->findMembers(
                SourceCode::fromString($updatedSource),
                $query
            );

            $result['replacements'] = $this->serializeReferenceList((string) $updatedSource, $replacedReferences);
        }

        return $result;
    }

    private function serializeReferenceList(string $code, MemberReferences $referenceList)
    {
        $references = [];
        /** @var $reference ClassRef */
        foreach ($referenceList as $reference) {
            $ref = $this->serializeReference($code, $reference);

            $references[] = $ref;
        }

        return $references;
    }

    private function serializeReference(string $code, MemberReference $reference)
    {
        list($lineNumber, $colNumber, $line) = $this->line($code, $reference->position()->start());
        return [
            'start' => $reference->position()->start(),
            'end' => $reference->position()->end(),
            'line' => $line,
            'line_no' => $lineNumber,
            'col_no' => $colNumber,
            'reference' => (string) $reference->methodName(),
            'class' => $reference->hasClass() ? (string) $reference->class() : null,
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

    private function replaceReferencesInCode(string $code, MemberReferences $list, string $replace): SourceCode
    {
        $code = SourceCode::fromString($code);

        return $this->methodReplacer->replaceMembers($code, $list, $replace);
    }

    private function createQuery(string $className = null, string $memberName = null)
    {
        $query = ClassMemberQuery::create();

        if ($className) {
            $query = $query->withClass($className);
        }

        if ($memberName) {
            $query = $query
                ->withMember($memberName);
        }

        return $query;
    }
}
