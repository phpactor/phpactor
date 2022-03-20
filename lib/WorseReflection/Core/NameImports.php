<?php

namespace Phpactor\WorseReflection\Core;

use RuntimeException;
use IteratorAggregate;
use ArrayIterator;

final class NameImports implements IteratorAggregate
{
    private $nameImports = [];

    private function __construct($nameImports)
    {
        foreach ($nameImports as $short => $item) {
            $this->add($short, $item);
        }
    }

    public static function fromNames(array $nameImports): NameImports
    {
        return new self($nameImports);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->nameImports);
    }

    public function getByAlias(string $alias)
    {
        if (!isset($this->nameImports[$alias])) {
            throw new RuntimeException(sprintf(
                'Unknown alias "%s", known aliases: "%s"',
                $alias,
                implode('", "', array_keys($this->nameImports))
            ));
        }

        return $this->nameImports[$alias];
    }

    public function resolveLocalName(Name $name): Name
    {
        foreach ($this->nameImports as $alias => $importedName) {
            assert($importedName instanceof Name);

            if (!$importedName->isAncestorOrSame($name)) {
                continue;
            }

            return $name->substitute($importedName, $alias);
        }

        return Name::fromString($name->short());
    }

    public function hasAlias(string $alias)
    {
        return isset($this->nameImports[$alias]);
    }

    private function add(string $short, Name $item): void
    {
        $this->nameImports[$short] = $item;
    }
}
