<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;

interface IndexWriter
{
    public function class(ClassRecord $class): void;

    public function timestamp(): void;

    public function function(FunctionRecord $function): void;
}
