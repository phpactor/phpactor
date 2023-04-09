<?php

namespace Phpactor\Indexer\Model\Record;

interface HasFlags
{
    public function flags(): int;
    public function hasFlag(int $flag): bool;
    public function addFlag(int $flag): self;
    public function setFlags(int $flags): self;
}
