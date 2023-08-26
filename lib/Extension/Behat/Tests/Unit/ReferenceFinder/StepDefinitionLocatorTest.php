<?php

namespace Phpactor\Extension\Behat\Tests\Unit\ReferenceFinder;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Behat\Behat\Context;
use Phpactor\Extension\Behat\Behat\Step;
use Phpactor\Extension\Behat\Behat\StepGenerator;
use Phpactor\Extension\Behat\Behat\StepParser;
use Phpactor\Extension\Behat\ReferenceFinder\StepDefinitionLocator;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Tests\Unit\LocationAssertions;
use Phpactor\TextDocument\TextDocumentBuilder;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class StepDefinitionLocatorTest extends TestCase
{
    use ProphecyTrait;
    use LocationAssertions;
    private const EXAMPLE_PATH = '/path/to.php';
    private const EXAMPLE_OFFSET = 6666;
    private const EXAMPLE_OFFSET_END = 7797;

    private StepDefinitionLocator $locator;

    /**
     * @var ObjectProphecy<StepGenerator>
     */
    private ObjectProphecy $generator;

    public function setUp(): void
    {
        $this->generator = $this->prophesize(StepGenerator::class);
        $this->locator = new StepDefinitionLocator(
            $this->generator->reveal(),
            new StepParser()
        );
    }

    /**
     * @dataProvider provideLocateDefinition
     */
    public function testLocateDefinition(string $step): void
    {
        $this->generator->getIterator()->will(function () use ($step) {
            yield new Step(
                new Context('foo', 'bar'),
                'myMethod',
                $step,
                self::EXAMPLE_PATH,
                self::EXAMPLE_OFFSET,
                self::EXAMPLE_OFFSET_END
            );
        });

        $text = <<<'EOT'
            Feature: Hello

                Scenario: Something
                    Given I have a scenario step
                    And my <>name is "Daniel"
                    When I jump to it's definition
                    Then my cursor should be on the step definition
            EOT
        ;

        [ $text, $offset ] = ExtractOffset::fromSource($text);

        $document = TextDocumentBuilder::create($text)->language('cucumber')->build();
        $offset = ByteOffset::fromInt((int)$offset);

        $location = $this->locator->locateDefinition($document, $offset);

        $sourceLocation = $location->first()->location();

        self::assertLocation($sourceLocation, self::EXAMPLE_PATH, self::EXAMPLE_OFFSET, self::EXAMPLE_OFFSET_END);
    }

    /**
     * @return Generator<int|string,array{string}>
     */
    public function provideLocateDefinition(): Generator
    {
        yield [
            'my name is ":name"',
        ];

        yield 'regex' => [
            '/my name is "\w+"/'
        ];

        yield 'turnip' => [
            'my name(s) is ":name"'
        ];
    }
}
