<?php

namespace Phpactor\Completion\Core\DocumentPrioritizer;

use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\TextDocumentUri;

/**
 * Compare the similarity between two paths and provide a suggestion priority
 * based on the similarity.
 *
 * This is used to compare the text document being edited and the suggestion.
 * Classes that are closer in the source tree will be suggested first.
 */
class SimilarityResultPrioritizer implements DocumentPrioritizer
{
    public function priority(?TextDocumentUri $one, ?TextDocumentUri $two): int
    {
        if (null === $one) {
            return Suggestion::PRIORITY_LOW;
        }

        if (null === $two) {
            return Suggestion::PRIORITY_LOW;
        }

        $e1 = explode('/', $one->path());
        $e2 = explode('/', $two->path());
        $e3 = array_intersect($e1, $e2);
        $max = max(count($e1), count($e2));
        $similarity = (1 / $max) * count($e3);

        $range = Suggestion::PRIORITY_LOW - Suggestion::PRIORITY_MEDIUM;

        return (int) (Suggestion::PRIORITY_MEDIUM + $range - $range * $similarity);
    }
}
