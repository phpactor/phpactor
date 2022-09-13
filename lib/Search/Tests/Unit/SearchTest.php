<?php

namespace Phpactor\Search\Tests\Unit;

use Phpactor\Search\Search;
use Phpactor\TestUtils\PHPUnit\TestCase;

class SearchTest extends TestCase
{
    public function testSearch(): void
    {
        (new Search())->search('class $a extends B');
    }
}
