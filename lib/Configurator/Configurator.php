<?php

namespace Phpactor\Configurator;

use Phpactor\Configurator\Model\Change;
use Phpactor\Configurator\Model\ChangeApplicator;
use Phpactor\Configurator\Model\ChangeSuggestor;
use Phpactor\Configurator\Model\Changes;
use RuntimeException;

class Configurator
{
    /**
     * @param list<ChangeSuggestor> $suggestors
     * @param list<ChangeApplicator> $applicators
     */
    public function __construct(
        private readonly array $suggestors,
        private readonly array $applicators
    ) {
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

    public function apply(Change|Changes $changes, bool $enable): void
    {
        $changes = $changes instanceof Changes ? $changes : Changes::from([$changes]);

        foreach ($changes as $change) {
            foreach ($this->applicators as $applicator) {
                if ($applicator->apply($change, $enable)) {
                    continue 2;
                }
            }

            throw new RuntimeException(sprintf(
                'Could not find change applicator for "%s"',
                $change::class
            ));
        }
    }
}
