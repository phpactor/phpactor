<?php

namespace Phpactor\ReferenceFinder\Tests\Unit\Search;

use PHPUnit\Framework\TestCase;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\ReferenceFinder\Search\NameSearchResultType;
use RuntimeException;

class NameSearchResultTest extends TestCase
{
    public function testThrowsExceptionOnInvalidType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is invalid');
        NameSearchResult::create('foobar', 'Foobar');
    }

    public function testCreateClassResult(): void
    {
        $result = NameSearchResult::create(NameSearchResultType::TYPE_CLASS, 'Foobar');
        self::assertInstanceOf(NameSearchResult::class, $result);
        self::assertTrue($result->type()->isClass());
    }

    public function testCreateFunctionResult(): void
    {
        $result = NameSearchResult::create(NameSearchResultType::TYPE_FUNCTION, 'Foobar');
        self::assertInstanceOf(NameSearchResult::class, $result);
        self::assertTrue($result->type()->isFunction());
    }
}
