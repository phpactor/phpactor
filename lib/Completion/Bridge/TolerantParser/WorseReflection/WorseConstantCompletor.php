<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Name;
use function sprintf;
use function str_starts_with;
use function var_export;

class WorseConstantCompletor implements TolerantCompletor
{
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (!$node instanceof QualifiedName) {
            return true;
        }

        $definedConstants = get_defined_constants();
        $partial = $node->getText();

        foreach ($definedConstants as $name => $value) {
            $name = Name::fromString((string) $name);

            if (str_starts_with($name->short(), $partial)) {
                yield Suggestion::createWithOptions(
                    $name->short(),
                    [
                        'type' => Suggestion::TYPE_CONSTANT,
                        'short_description' => sprintf('%s = %s', $name->full(), var_export($value, true))
                    ]
                );
            }
        }

        return true;
    }
}
