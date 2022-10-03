<?php

namespace Phpactor\Extension\Rpc\Tests\Unit\Editor;

use Phpactor\Extension\Rpc\Response\ReturnChoiceResponse;
use Phpactor\Extension\Rpc\Response\ReturnOption;
use PHPUnit\Framework\TestCase;

class ReturnChoiceActionTest extends TestCase
{
    public function testCreate(): void
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
