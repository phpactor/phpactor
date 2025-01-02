<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Record;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Record\MemberRecord;
use RuntimeException;
use ValueError;

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
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('"asd" is not a valid backing value for enum');
        MemberRecord::fromIdentifier('asd#member');
    }
}
