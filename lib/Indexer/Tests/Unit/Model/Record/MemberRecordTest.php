<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Record;

use Phpactor\Indexer\Model\Record\MemberRecord;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MemberRecordTest extends TestCase
{
    public function testExceptionOnInvalidIdentifier(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid member identifier');
        MemberRecord::fromIdentifier('member');
    }

    public function testExceptionOnInvalidType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid member type');
        MemberRecord::fromIdentifier('asd#member');
    }
}
