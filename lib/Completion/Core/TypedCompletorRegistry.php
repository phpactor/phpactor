<?php

namespace Phpactor\Completion\Core;

class TypedCompletorRegistry
{
    /**
     * @var array<string, Completor>
     */
    private array $completors;

    /**
     * Map should be from language ID to completor for that language
     * (can be a chain completor):
     *
     * @param array<string, Completor> $completorMap
     */
    public function __construct(array $completorMap)
    {
        foreach ($completorMap as $type => $completor) {
            $this->add($type, $completor);
        }
    }

    public function completorForType(string $type): Completor
    {
        if (!isset($this->completors[$type])) {
            return new ChainCompletor([]);
        }

        return $this->completors[$type];
    }

    private function add(string $type, Completor $completor): void
    {
        $this->completors[$type] = $completor;
    }
}
