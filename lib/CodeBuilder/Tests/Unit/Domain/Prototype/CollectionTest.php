<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\Collection;
use stdClass;

class CollectionTest extends TestCase
{
    public function testGetThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown test "foo", known items');
        $collection = TestCollection::fromArray([
            'one' => new stdClass()
        ]);

        $collection->get('foo');
    }
}

/**
 * @extends Collection<stdClass>
 */
class TestCollection extends Collection
{
    public static function fromArray(array $items)
    {
        return new self($items);
    }

    protected function singularName(): string
    {
        return 'test';
    }
}
