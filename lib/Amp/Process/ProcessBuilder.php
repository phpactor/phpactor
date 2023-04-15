<?php

namespace Phpactor\Amp\Process;

use Amp\Process\Process;

final class ProcessBuilder
{
    /**
     * @var array<string,mixed>
     */
    private array $env = [];

    private ?string $cwd = null;

    private bool $mergeParentEnv = false;

    /**
     * @param list<string> $args
     */
    private function __construct(private array $args)
    {
    }

    /**
     * @param list<string> $args
     */
    public function cmd(array $args): self
    {
        $this->args = $args;
        return $this;
    }

    public function cwd(string $cwd): self
    {
        $this->cwd = $cwd;
        return $this;
    }
    /**
     * @param array<string,mixed> $env
     */
    public function env(array $env): self
    {
        $this->env = $env;
        return $this;
    }

    public function mergeParentEnv(): self
    {
        $this->mergeParentEnv = true;
        return $this;
    }

    public function build(): Process
    {
        $env = $this->env;
        if ($this->mergeParentEnv) {
            $env = array_merge(getenv(), $env);
        }
        return new Process($this->args, $this->cwd, $env);
    }

    /**
     * @param list<string> $args
     */
    public static function create(array $args): self
    {
        return new self($args);
    }
}
