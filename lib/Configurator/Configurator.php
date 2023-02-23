<?php

namespace Phpactor\Configurator;

class Configurator
{
    /**
     * @param list<ChangeSuggestor> $suggestors
     * @param list<ChangeApplicator> $applicators
     */
    public function __construct(private array $suggestors, private array $applicators)
    {
    }

    public function suggestChanges(): Changes
    {
        $changes = [];
        foreach ($this->suggestors as $suggestor) {
            foreach ($suggestor->suggestChanges() as $change) {
                $changes[] = $change;
            }
        }

        return new Changes($changes);
    }

    public function apply(Changes $changes): void
    {
        foreach ($this->applicators as $applicator) {
            foreach ($changes as $change) {
                $applicator->apply($change);
            }
        }
    }
}
