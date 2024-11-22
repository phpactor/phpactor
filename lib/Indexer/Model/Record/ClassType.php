<?php

declare(strict_types=1);

namespace Phpactor\Indexer\Model\Record;

enum ClassType: string
{
    case TYPE_CLASS = 'class';
    case TYPE_INTERFACE = 'interface';
    case TYPE_TRAIT = 'trait';
    case TYPE_ENUM = 'enum';
}
