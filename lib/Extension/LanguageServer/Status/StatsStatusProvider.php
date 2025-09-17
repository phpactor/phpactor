<?php

namespace Phpactor\Extension\LanguageServer\Status;

use Phpactor\Stats;

final class StatsStatusProvider implements StatusProvider
{
    public function title(): string
    {
        return 'counters';
    }

    public function provide(): array
    {
        return Stats::toArray();
    }
}
