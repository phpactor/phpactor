<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\ServiceLocator;
use RuntimeException;

class StubFileMemberProvider implements ReflectionMemberProvider
{
    /**
     * @var array<string,ReflectionClassLike>
     */
    private array $stubClasses = [];

    private bool $initialized = false;

    /**
     * @param list<string> $stubFiles
     */
    public function __construct(private array $stubFiles)
    {
    }

    public function provideMembers(ServiceLocator $locator, ReflectionClassLike $class): ReflectionMemberCollection
    {
        $this->buildMap($locator);

        if (!isset($this->stubClasses[$class->name()->__toString()])) {
            return ChainReflectionMemberCollection::fromCollections([]);
        }

        $stubClass = $this->stubClasses[$class->name()->__toString()];
        return $stubClass->members();
    }

    private function buildMap(ServiceLocator $locator): void
    {
        if ($this->initialized === true) {
            return;
        }

        $classes = [];
        foreach ($this->stubFiles as $stubFile) {
            try {
                $document = TextDocumentBuilder::fromUri($stubFile)->language('php')->build();
            } catch (RuntimeException) {
                // depend on validation on startup rather than break everything
                continue;
            }
            foreach ($locator->stubReflector()->reflectClassesIn(
                $document,
            ) as $class) {
                $classes[$class->name()->__toString()] = $class;
            }
        }

        $this->stubClasses = $classes;
        $this->initialized = true;
    }
}
