<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateAccessorsCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateMethodCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Reflector;
use function Amp\call;

class GenerateAccessorsProvider implements CodeActionProvider
{
    private Reflector $reflector;
    public const KIND = 'quickfix.generate_accessors';

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    
    public function kinds(): array
    {
        return [
             self::KIND
         ];
    }
    
    public function provideActionsFor(TextDocumentItem $textDocument, Range $range): Promise
    {
        return call(function () use ($range, $textDocument) {
            $startOffset = PositionConverter::positionToByteOffset($range->start, $textDocument->text)->toInt();
            $endOffset = PositionConverter::positionToByteOffset($range->end, $textDocument->text)->toInt();

            $classes = $this->reflector->reflectClassesIn($textDocument->text);

            if ($classes->count() === 0) {
                return [];
            }

            // TODO: Class at offset
            $reflectionClass = $classes->first();

            $propertyNames = [];
            foreach ($reflectionClass->properties() as $property) {
                assert($property instanceof ReflectionProperty);
                if ($property->position()->start() < $startOffset || $property->position()->end() > $endOffset) {
                    continue;
                }
                $propertyNames[] = $property->name();
            }

            if (empty($propertyNames)) {
                return [];
            }

            return [
                CodeAction::fromArray([
                    'title' => sprintf('Generate %s accessor(s)', count($propertyNames)),
                    'kind' => self::KIND,
                    'command' => new Command(
                        sprintf('Generate %s accessor(s)', count($propertyNames)),
                        GenerateAccessorsCommand::NAME,
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
