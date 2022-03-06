<?php

namespace Phpactor\Indexer\Adapter\Php\InMemory;

use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;

class InMemoryRepository
{

    /**
     * @var int
     */
    public $lastUpdate = 0;
    /**
     * @var array<ClassRecord>
     */
    private $classes = [];

    /**
     * @var array<FunctionRecord>
     */
    private $functions = [];

    public function putClass(ClassRecord $class): void
    {
        $this->classes[$class->fqn()->__toString()] = $class;
    }

    public function putFunction(FunctionRecord $function): void
    {
        $this->functions[$function->fqn()->__toString()] = $function;
    }

    public function getClass(string $fqn): ?ClassRecord
    {
        if (!isset($this->classes[$fqn])) {
            return null;
        }

        return $this->classes[$fqn];
    }

    public function reset(): void
    {
        $this->classes = [];
        $this->functions = [];
    }

    public function getFunction(string $fqn): ?FunctionRecord
    {
        if (!isset($this->functions[$fqn])) {
            return null;
        }

        return $this->functions[$fqn];
    }
}
