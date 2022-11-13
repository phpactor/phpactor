<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Util;

use Microsoft\PhpParser\Node;
use Phpactor\CodeBuilder\Domain\Prototype\UseStatement;

final class UseStatementDeduplicator
{
    /**
     * @param array<UseStatement> $usePrototypes
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

            // We have a duplicate to handle.
            $usePrototype = new UseStatement(
                $usePrototype->name(),
                $this->getAlias($existingNames, (string) $usePrototype->name()),
                $usePrototype->type()
            );
        }

        return $usePrototypes;
    }

    /**
     * Trying to find an alias that will not make a collision.
     *
     * If you are trying to import A\B\C\D it is going to try to alias it as:
     * use A\B\C\D as AB;
     * if that is taken
     * use A\B\C\D as ABC;
     *
     * If all of the above doesn't work, then it has to be an already existing import which is handles somewhere else.
     *
     * @param array<string, string> $existingImports
     */
    private function getAlias(array $existingImports, string $nameToImport): string
    {
        $nameparts = explode('\\', $nameToImport);
        $shortName = end($nameparts);

        if (count($nameparts) === 1) {
            return 'Aliased'.$shortName;
        }

        for ($i = 1; $i < count($nameparts); ++$i) {
            $newAlias = implode('', array_slice($nameparts, 0, $i)).$shortName;
            if (!array_key_exists($newAlias, $existingImports)) {
                break;
            }
        }


        return $newAlias;
    }
}
