<?php

namespace Phpactor\Extension\Behat\Tests\Integration\Adapter\Worse;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Behat\Adapter\Worse\WorseContextClassResolver;
use Phpactor\Extension\Behat\Adapter\Worse\WorseStepFactory;
use Phpactor\Extension\Behat\Behat\Context;
use Phpactor\Extension\Behat\Behat\Step;
use Phpactor\Extension\Behat\Behat\StepParser;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseStepFactoryTest extends TestCase
{
    public function testGeneratesSteps(): void
    {
        $path = __DIR__ . '/TestContext.php';
        $reflector = ReflectorBuilder::create()->addSource(TextDocumentBuilder::fromUri($path)->build())->build();
        $stepGenerator = new WorseStepFactory($reflector, new WorseContextClassResolver($reflector));
        $parser = new StepParser();
        $context = new Context('default', TestContext::class);
        $steps = iterator_to_array($stepGenerator->generate($parser, [ $context ]));

        $this->assertEquals([
            new Step($context, 'givenThatThis', 'that I visit Berlin', Location::fromPathAndOffsets($path, 150, 199)),
            new Step($context, 'shouldRun', 'I should run to Weisensee', Location::fromPathAndOffsets($path, 260, 305)),
        ], $steps);
    }
}
