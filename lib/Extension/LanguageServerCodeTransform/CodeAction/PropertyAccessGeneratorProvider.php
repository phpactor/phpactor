<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Reflector;
use function Amp\call;

class PropertyAccessGeneratorProvider implements CodeActionProvider
{
    public function __construct(
        private string $kind,
        private string $command,
        private string $generatorRole,
        private Reflector $reflector
    ) {
    }

    public function kinds(): array
    {
        return [
            $this->kind,
        ];
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($range, $textDocument) {
            // CoC will select the entire document if no range selected
            if ($range->start->line === 0 && $range->start->character === 0) {
                return [];
            }
            $startOffset = PositionConverter::positionToByteOffset($range->start, $textDocument->text)->toInt();
            $endOffset = PositionConverter::positionToByteOffset($range->end, $textDocument->text)->toInt();

            $classes = $this->reflector->reflectClassesIn($textDocument->text);

            if ($classes->count() === 0) {
                return [];
            }

            // TODO: Class at offset
            $reflectionClass = $classes->first();

            if (!$reflectionClass instanceof ReflectionClass) {
                return [];
            }

            $propertyNames = [];
            foreach ($reflectionClass->properties() as $property) {
                assert($property instanceof ReflectionProperty);
                if ($property->position()->start()->toInt() < $startOffset || $property->position()->end()->toInt() > $endOffset) {
                    continue;
                }
                $propertyNames[] = $property->name();
            }

            if (empty($propertyNames)) {
                return [];
            }

            $title = sprintf(
                'Generate %s %s(s)',
                count($propertyNames),
                $this->generatorRole
            );

            return [
                CodeAction::fromArray([
                    'title' => $title,
                    'kind' => $this->kind,
                    'command' => new Command(
                        $title,
                        $this->command,
                        [
                            $textDocument->uri,
                            $startOffset,
                            $propertyNames,
                        ]
                    )
                ])
            ];
        });
    }
}
