<?php

namespace Phpactor\Extension\Rpc\Tests\Unit\Response\Input;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;

class ChoiceInputTest extends TestCase
{
    public function testCreateWithShortcut(): void
    {
        $choice = ChoiceInput::fromNameLabelChoices('foo', 'foobar', [
            'one',
            'two',
        ])->withKeys([
            'one' => 'o',
            'two' => 't',
        ]);
        self::assertEquals([
            'label' => 'foobar',
            'choices' => [
                0 => 'one',
                1 => 'two',
            ],
            'default' => null,
            'keyMap' => [
                'one' => 'o',
                'two' => 't',
            ],
        ], $choice->parameters());
    }
}
