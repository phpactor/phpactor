<?php

namespace Phpactor\Completion\Tests\Integration;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

abstract class CompletorTestCase extends IntegrationTestCase
{
    use ArraySubsetAsserts;

    public function assertCouldNotComplete(string $source): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $completor = $this->createCompletor($source);
        $suggestions = $completor->complete(
            TextDocumentBuilder::create($source)->language('php')->uri('file:///tmp/test')->build(),
            ByteOffset::fromInt($offset)
        );

        $array = iterator_to_array($suggestions);
        $this->assertEmpty($array);
        $this->assertTrue($suggestions->getReturn());
    }

    abstract protected function createCompletor(string $source): Completor;

    protected function assertComplete(string $source, array $expected, bool $isComplete = true): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $completor = $this->createCompletor($source);
        $suggestionGenerator = $completor->complete(
            TextDocumentBuilder::create($source)->language('php')->uri('file:///tmp/test')->build(),
            ByteOffset::fromInt((int)$offset)
        );
        $suggestions = iterator_to_array($suggestionGenerator, false);
        usort($suggestions, function (Suggestion $suggestion1, Suggestion $suggestion2) {
            return $suggestion1->name() <=> $suggestion2->name();
        });

        $this->assertCount(count($expected), $suggestions);
        foreach ($expected as $index => $expectedSuggestion) {
            $actual = $suggestions[$index]->toArray();
            $this->assertArraySubset($expectedSuggestion, $actual);
            if (array_key_exists('snippet', $expectedSuggestion) === false) {
                self::assertEmpty($actual['snippet'], 'got unexpected snippet "' . $actual['snippet'] . '"');
            }
        }

        $this->assertCount(count($expected), $suggestions);
        $this->assertEquals($isComplete, $suggestionGenerator->getReturn(), '"is complete" was as expected');
    }
}
