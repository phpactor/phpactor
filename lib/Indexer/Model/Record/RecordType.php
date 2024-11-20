<?php

declare(strict_types=1);

namespace Phpactor\Indexer\Model\Record;

enum RecordType : string
{
    case CLASS_ = 'class';
    case CONSTANT = 'constant';
    case FILE = 'file';
    case FUNCTION = 'function';
    case MEMBER = 'member';
}
