<?php

namespace Phpactor\Extension\ExtensionManager\Model;

class ExtensionState
{
    public const STATE_NOT_INSTALLED = 0;
    public const STATE_PRIMARY = 2;
    public const STATE_SECONDARY = 4;

    /**
     * @var int
     */
    private $state;

    public function __construct(int $state)
    {
        $this->state = $state;
    }

    public function isInstalled(): bool
    {
        return $this->state !== self::STATE_NOT_INSTALLED;
    }

    public function isPrimary()
    {
        return (bool) ($this->state & self::STATE_PRIMARY);
    }

    public function isSecondary()
    {
        return (bool) ($this->state & self::STATE_SECONDARY);
    }
}
