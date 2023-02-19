<?php

namespace Phpactor\Extension\LanguageServer\TaskExecutor;

use RuntimeException;

class TaskExecutors
{
    /**
     * @param array<string,TaskExecutor> $executors
     */
    public function __construct(private array $executors)
    {
    }

    public function get(string $name): TaskExecutor
    {
        if (isset($this->executors[$name])) {
            throw new RuntimeException(sprintf(
                'Unknown tas executor "%s", known executors: "%s"',
                $name,
                implode('", "', array_keys($this->executors))
            ));
        }

        return $this->executors[$name];
    }
}
