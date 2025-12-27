<?php

namespace Phpactor\Extension\ClassMover\Application;

use Phpactor\ClassMover\Domain\MemberFinder;
use Phpactor\ClassMover\Domain\MemberReplacer;
use Phpactor\ClassMover\Domain\Reference\MemberReference;
use Phpactor\ClassMover\Domain\Reference\MemberReferences;
use Phpactor\ClassMover\Domain\SourceCode;
use Phpactor\Extension\ClassMover\Application\Finder\FileFinder;
use Phpactor\Extension\Core\Application\Helper\ClassFileNormalizer;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\ClassMover\Domain\Model\ClassMemberQuery;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\WorseReflection\Reflector;

class ClassMemberReferences
{
    public function __construct(
        private readonly ClassFileNormalizer $classFileNormalizer,
        private readonly MemberFinder $memberFinder,
        private readonly MemberReplacer $memberReplacer,
        private readonly FilesystemRegistry $filesystemRegistry,
        private readonly Reflector $reflector
    ) {
    }

    public function findOrReplaceReferences(
        string $scope,
        ?string $class = null,
        ?string $memberName = null,
        ?string $memberType = null,
        ?string $replace = null,
        bool $dryRun = false
    ) {
        $className = $class ? $this->classFileNormalizer->normalizeToClass($class) : null;
        $reflection = $className ? $this->reflector->reflectClassLike($className) : null;
        $filesystem = $this->filesystemRegistry->get($scope);

        $filePaths = (new FileFinder())->filesFor($filesystem, $reflection, $memberName);

        $results = [];
        foreach ($filePaths as $filePath) {
            $references = $this->referencesInFile($filesystem, $filePath, $className, $memberName, $memberType, $replace, $dryRun);

            if ($references['references'] === [] && $references['risky_references'] === []) {
                continue;
            }

            $references['file'] = (string) $filePath;
            $results[] = $references;
        }

        return [
            'references' => $results
        ];
    }

    public function replaceInSource(
        string $source,
        string $class,
        string $memberName,
        string $memberType,
        string $replacement
    ):string {
        $className = $class ? $this->classFileNormalizer->normalizeToClass($class) : null;
        $query = $this->createQuery($className, $memberName, $memberType);

        $referenceList = $this->memberFinder->findMembers(
            SourceCode::fromString($source),
            $query
        );
        return (string) $this->replaceReferencesInCode($source, $referenceList->withClasses(), $replacement);
    }

    /**
     * @return array{references: array<mixed>, risky_references: array<mixed>, replacements: array<mixed>}
     */
    private function referencesInFile(
        Filesystem $filesystem,
        $filePath,
        ?string $className = null,
        ?string $memberName = null,
        ?string $memberType = null,
        ?string $replace = null,
        bool $dryRun = false
    ): array {
        $code = $filesystem->getContents($filePath);

        $query = $this->createQuery($className, $memberName, $memberType);

        $referenceList = $this->memberFinder->findMembers(
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

            $query = $this->createQuery($className, $replace, $memberType);

            $replacedReferences = $this->memberFinder->findMembers(
                SourceCode::fromString($updatedSource),
                $query
            );

            $result['replacements'] = $this->serializeReferenceList((string) $updatedSource, $replacedReferences);
        }

        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function serializeReferenceList(string $code, MemberReferences $referenceList): array
    {
        $references = [];
        /** @var MemberReference $reference */
        foreach ($referenceList as $reference) {
            $ref = $this->serializeReference($code, $reference);

            $references[] = $ref;
        }

        return $references;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeReference(string $code, MemberReference $reference): array
    {
        [$lineNumber, $colNumber, $line] = $this->line($code, $reference->position()->start());
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

    /**
     * @return array{int, int, string}
     */
    private function line(string $code, int $offset):array
    {
        $lines = explode("\n", $code);
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

        return $this->memberReplacer->replaceMembers($code, $list, $replace);
    }

    private function createQuery(?string $className = null, ?string $memberName = null, $memberType = null): ClassMemberQuery
    {
        $query = ClassMemberQuery::create();

        if ($className) {
            $query = $query->withClass($className);
        }

        if ($memberName) {
            $query = $query->withMember($memberName);
        }

        if ($memberType) {
            $query = $query->withType($memberType);
        }

        return $query;
    }
}
