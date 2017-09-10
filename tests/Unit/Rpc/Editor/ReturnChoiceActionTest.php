<?php

namespace Phpactor\Tests\Unit\Rpc\Editor;

use PHPUnit\Framework\TestCase;
use Phpactor\Rpc\Editor\ReturnOption;
use Phpactor\Rpc\Editor\ReturnChoiceAction;

class ReturnChoiceActionTest extends TestCase
{
    public function testCreate()
    {
        $option1 = ReturnOption::fromNameAndValue(
            'one',
            1000
        );

        $returnChoice = ReturnChoiceAction::fromOptions([$option1]);

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
