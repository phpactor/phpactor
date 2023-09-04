<?php

namespace Phpactor\Extension\Behat\Tests\Unit\Behat;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Behat\Behat\Context;
use Phpactor\Extension\Behat\Behat\Step;
use Phpactor\Extension\Behat\Behat\StepScorer;
use Phpactor\TextDocument\Location;

class StepScorerTest extends TestCase
{
    /**
     * @dataProvider provideSortSteps
     * @param array<string,int> $expectedScores
     * @param array<Step> $exampleSteps
     */
    public function testSortSteps(array $exampleSteps, string $partial, array $expectedScores): void
    {
        $sort = new StepScorer();
        $score = $sort->scoreSteps($exampleSteps, $partial);
        $this->assertEquals($expectedScores, $score);
    }

    /**
     * @return Generator<array{array<Step>,string,array<string,int>}>
     */
    public function provideSortSteps(): Generator
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


    private function createStep(string $string): Step
    {
        $context = new Context('foo', 'bar');
        return new Step($context, 'foo', $string, Location::fromPathAndOffsets('/path/to.php', 1, 5));
    }
}
