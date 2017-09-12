<?php

namespace Phpactor\Rpc\Handler;

final class ClassReferencesHandler43 implements \IteratorAggregate
{
    private $classreferenceshandler43 = [];

    private function __construct($classreferenceshandler43)
    {
        foreach ($classreferenceshandler43 as $item) {
            $this->add($item);
        }
    }

    public static function fromClassReferencesHandler43(array $classreferenceshandler43): ClassReferencesHandler43
    {
         return new self($classreferenceshandler43);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->classreferenceshandler43);
    }

    private function add($item)
    {
        $this->classreferenceshandler43[] = $item;
    }
}