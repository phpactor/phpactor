<?php

namespace Phpactor\Indexer\Model\Record;

use Phpactor\Indexer\Model\Name\FullyQualifiedName;

interface HasFullyQualifiedName extends HasShortName
{
    public function fqn(): FullyQualifiedName;
}
