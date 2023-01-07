<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Util;

use Microsoft\PhpParser\Node;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatement;

final class UseStatementDeduplicator
{
    private array $aliasedClasses = [];

    /**
     * Get a list of classes that have been newly aliased in the importing process
     *
     * @return array<string, string>
     */
    public function getAliasedClasses(): array
    {
        return $this->aliasedClasses;
    }

    /**
     * @param array<UseStatement> $usePrototypes
     *
     * @return UseStatement[]
     */
    public function deduplicate(Node $lastNode, array $usePrototypes): array
    {
        $existingNames = (new ImportedNames($lastNode))->aliasMap();

        foreach ($usePrototypes as &$usePrototype) {
            if ($usePrototype->hasAlias()) {
                $shortName = $usePrototype->alias();
            } else {
                $nameparts = explode('\\', (string) $usePrototype->name());
                $shortName = end($nameparts);
            }

            // If the short name is not imported yet, skip it.
            if (!array_key_exists($shortName, $existingNames)) {
                continue;
            }

            $newAlias = str_replace('\\', '', (string) $usePrototype->name());

            // Update the current use statement with the aliased one
            $usePrototype = new UseStatement( $usePrototype->name(), $newAlias, $usePrototype->type());

            $this->aliasedClasses[(string) $usePrototype->name()] = $newAlias;
        }

        return $usePrototypes;
    }
}
