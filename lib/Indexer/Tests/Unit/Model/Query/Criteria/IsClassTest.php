<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;
use PHPUnit\Framework\TestCase;

class IsClassTest extends TestCase
{
    public function testIsClass(): void
    {
        self::assertTrue(Criteria::isClass()->isSatisfiedBy(ClassRecord::fromName('foobar')));
        self::assertFalse(Criteria::isClass()->isSatisfiedBy(FunctionRecord::fromName('foobar')));
    }
}
