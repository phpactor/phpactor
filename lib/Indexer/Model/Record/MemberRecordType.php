<?php

declare(strict_types=1);

namespace Phpactor\Indexer\Model\Record;

enum MemberRecordType: string
{
    case TYPE_METHOD = 'method';
    case TYPE_CONSTANT = 'constant';
    case TYPE_PROPERTY = 'property';
}
