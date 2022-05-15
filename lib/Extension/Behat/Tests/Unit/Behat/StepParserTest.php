<?php

namespace Phpactor\Extension\Behat\Tests\Unit\Behat;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Behat\Behat\StepParser;

class StepParserTest extends TestCase
{
    /**
     * @dataProvider provideSteps
     */
    public function testParsesPhpStepsDefinitions(string $docblock, array $expected)
    {
        $parser = new StepParser();
        $steps = $parser->parseSteps($docblock);
        $this->assertEquals($expected, $steps);
    }

    public function provideSteps()
    {
        yield [
            '* @Given I visit Berlin',
            [
                'I visit Berlin'
            ]
        ];

        yield [
            <<<'EOT'
/**
 * @Given I visit Berlin
 * @And I go to Alexanderplatz
 * @When climb up the Fernsehturm
 * @Then I will see things
 * @But I will not know what they are
 */
EOT
            , [
                'I visit Berlin',
                'I go to Alexanderplatz',
                'climb up the Fernsehturm',
                'I will see things',
                'I will not know what they are',
            ]
        ];
    }
}
