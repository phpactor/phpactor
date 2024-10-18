<?php

namespace Phpactor\Extension\Behat\Completor;

use Generator;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Range;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\Behat\Behat\Step;
use Phpactor\Extension\Behat\Behat\StepGenerator;
use Phpactor\Extension\Behat\Behat\StepParser;
use Phpactor\Extension\Behat\Behat\StepScorer;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class FeatureStepCompletor implements Completor
{
    private StepScorer $stepSorter;

    public function __construct(
        private StepGenerator $generator,
        private StepParser $parser,
        ?StepScorer $stepSorter = null
    ) {
        $this->stepSorter = $stepSorter ?: new StepScorer();
    }


    public function complete(TextDocument $source, ByteOffset $byteOffset): Generator
    {
        $currentLine = $this->lineForOffset($source->__toString(), $byteOffset->toInt());
        $parsed = $this->parser->parseSteps($currentLine);

        if ($parsed === []) {
            return false;
        }
        $partial = $parsed[0];

        $steps = iterator_to_array($this->generator);

        if ($partial) {
            $scores = $this->stepSorter->scoreSteps($steps, $partial);
            usort($steps, function (Step $step1, Step $step2) use ($scores) {
                return $scores[$step2->pattern()] <=> $scores[$step1->pattern()];
            });
        }

        /** @var Step $step */
        foreach ($steps as $step) {
            $suggestion = $step->pattern();

            if (preg_match('{^' . $partial. '}i', $suggestion)) {
                $suggestion = substr($suggestion, strlen($partial));
            }

            $startOffset = $byteOffset->toInt() - strlen($partial);

            yield Suggestion::createWithOptions($suggestion, [
                'label' => $step->pattern(),
                'short_description' => $step->context()->class(),
                'type' => Suggestion::TYPE_SNIPPET,
                'range' => Range::fromStartAndEnd(
                    $startOffset,
                    $startOffset + strlen($partial)
                )
            ]);
        }

        return false;
    }

    private function lineForOffset(string $source, int $byteOffset): string
    {
        $length = 0;
        $last = '';
        $lines = preg_split('/$(\R?^)/m', $source, -1, PREG_SPLIT_OFFSET_CAPTURE);
        if (false === $lines) {
            return $source;
        }

        foreach ($lines as $line) {
            [ $line, $offset] = $line;
            $offset = (int) $offset;

            if ($offset + strlen($line) >= $byteOffset) {
                return $line;
            }
        }

        return '';
    }
}
