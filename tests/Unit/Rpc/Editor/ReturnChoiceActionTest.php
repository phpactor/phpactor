<?php

namespace Phpactor\Tests\Unit\Rpc\Editor;

use PHPUnit\Framework\TestCase;
use Phpactor\Rpc\Response\ReturnOption;
use Phpactor\Rpc\Response\ReturnChoiceResponse;

class ReturnChoiceActionTest extends TestCase
{
    public function testCreate()
    {
        $option1 = ReturnOption::fromNameAndValue(
            'one',
            1000
        );

        $returnChoice = ReturnChoiceResponse::fromOptions([$option1]);

        $this->assertEquals([
            'choices' => [
                [
                    'name' => 'one',
                    'value' => 1000,
                ],
            ],
        ], $returnChoice->parameters());
    }
}
