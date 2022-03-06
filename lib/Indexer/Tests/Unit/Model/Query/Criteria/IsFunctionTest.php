<?php

namespace Phpactor\Indexer\Tests\Unit\Model\Query\Criteria;

use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;

class IsFunctionTest extends TestCase
{
    public function testIsFunction(): void
    {
        self::assertFalse(Criteria::isFunction()->isSatisfiedBy(ClassRecord::fromName('foobar')));
        self::assertTrue(Criteria::isFunction()->isSatisfiedBy(FunctionRecord::fromName('foobar')));
    }
}
