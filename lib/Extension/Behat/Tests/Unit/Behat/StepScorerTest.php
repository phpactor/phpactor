<?php

namespace Phpactor\Extension\Behat\Tests\Unit\Behat;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Behat\Behat\Context;
use Phpactor\Extension\Behat\Behat\Step;
use Phpactor\Extension\Behat\Behat\StepScorer;

class StepScorerTest extends TestCase
{
    /**
     * @dataProvider provideSortSteps
     */
    public function testSortSteps(array $exampleSteps, string $partial, array $expectedScores)
    {
        $sort = new StepScorer();
        $score = $sort->scoreSteps($exampleSteps, $partial);
        $this->assertEquals($expectedScores, $score);
    }

    public function provideSortSteps()
    {
        yield [
            [
                $this->createStep('midnight'),
                $this->createStep('weary'),
            ],
            'midnight',
            [
                'midnight' => 1,
                'weary' => 0,
            ]
        ];

        yield [
            [
                $this->createStep('midnight'),
                $this->createStep('weary'),
            ],
            'mid',
            [
                'midnight' => 1,
                'weary' => 0,
            ]
        ];

        yield [
            [
                $this->createStep('Once upon a midnight'),
                $this->createStep('Once time'),
            ],
            'Once mid',
            [
                'Once upon a midnight' => 2,
                'Once time' => 1,
            ]
        ];

        yield [
            [
                $this->createStep('Once   upon a midnight'),
            ],
            'Once mid  ',
            [
                'Once   upon a midnight' => 2,
            ]
        ];
    }

    private function createStep(string $string)
    {
        $context = new Context('foo', 'bar');
        return new Step($context, 'foo', $string, 'path/to.php', 1);
    }
}
