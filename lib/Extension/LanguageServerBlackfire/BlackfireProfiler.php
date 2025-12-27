<?php

namespace Phpactor\Extension\LanguageServerBlackfire;

use Blackfire\Client;
use Blackfire\Probe;

class BlackfireProfiler
{
    private ?Probe $probe = null;

    private bool $profiling = false;

    private bool $probing = false;

    public function __construct(private readonly Client $blackfire)
    {
    }

    public function enable(): void
    {
        if ($this->probing) {
            return;
        }
        if ($this->probe) {
            $this->probe->enable();
            $this->probing = true;
        }
    }

    public function disable(): void
    {
        if ($this->probe && $this->probing) {
            $this->probe->disable();
            $this->probing = false;
        }
    }

    /**
     * Return URL of profile
     */
    public function done(): string
    {
        $profile = $this->blackfire->endProbe($this->probe);
        $this->probing = false;
        $this->probe = null;
        return $profile->getUrl();
    }

    public function start(): void
    {
        $this->getProbe();
        $this->profiling = true;
    }

    /**
     * Return true if profiling has been started
     */
    public function started(): bool
    {
        return $this->profiling;
    }

    private function getProbe(): Probe
    {
        if ($this->probe) {
            return $this->probe;
        }

        $this->probe = $this->blackfire->createProbe(null, false);
        return $this->probe;
    }
}
