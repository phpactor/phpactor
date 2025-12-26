<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\ClassQualifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifiable;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;

class WorseDeclaredClassCompletor implements TolerantCompletor, TolerantQualifiable
{
    public function __construct(
        private readonly ClassReflector $reflector,
        private readonly ObjectFormatter $formatter
    ) {
    }


    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $classes = get_declared_classes();
        $classes = array_filter($classes, function ($class) use ($node) {
            $name = Name::fromString($class);
            return str_starts_with($name->short(), $node->getText());
        });

        foreach ($classes as $class) {
            try {
                $reflectionClass = $this->reflector->reflectClass($class);
            } catch (NotFound) {
                continue;
            }

            yield Suggestion::createWithOptions(
                $reflectionClass->name()->short(),
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'short_description' => $this->formatter->format($reflectionClass),
                    'documentation' => $reflectionClass->docblock()->formatted()
                ]
            );
        }

        return true;
    }

    public function qualifier(): TolerantQualifier
    {
        return new ClassQualifier();
    }
}
