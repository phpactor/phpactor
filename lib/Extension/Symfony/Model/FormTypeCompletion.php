<?php

namespace Phpactor\Extension\Symfony\Model;

use Generator;
use Phpactor\Completion\Core\Suggestion;

final class FormTypeCompletion
{
    /**
     * @param array<string> $options
     */
    public function __construct(
        public ?string $parentClass,
        public array $options = []
    ) {
    }

    public function getCompletions(): Generator
    {
        foreach ($this->options as $option) {
            yield Suggestion::createWithOptions(
                $option,
                [
                    'label' => $option,
                    'short_description' => '',
                    'documentation' => '',
                    'type' => Suggestion::TYPE_CONSTANT,
                    'priority' => 555,
                ]
            );
        }

        return true;
    }
}
