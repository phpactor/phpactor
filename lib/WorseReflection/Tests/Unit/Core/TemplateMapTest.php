<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\TypeFactory;

class TemplateMapTest extends TestCase
{
    public function testMapArguments(): void
    {
        $templateMap = new TemplateMap(['TKey' => TypeFactory::undefined(), 'TValue' => TypeFactory::unknown()]);
        $mapped = $templateMap->mapArguments([TypeFactory::string(), TypeFactory::int()]);

        self::assertEquals(new TemplateMap(['TKey' => TypeFactory::string(), 'TValue' => TypeFactory::int()]), $mapped);
    }
}
