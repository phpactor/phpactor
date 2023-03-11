<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateDecoratorCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Reflector;
use function Amp\call;

class GenerateDecoratorProvider implements CodeActionProvider
{
    public const KIND = 'quickfix.generate_decorator';

    public function __construct(private Reflector $reflector)
    {
    }

    public function kinds(): array
    {
        return [
            self::KIND,
        ];
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument) {
            $classes = $this->reflector->reflectClassesIn(TextDocumentConverter::fromLspTextItem($textDocument));
            if (count($classes->classes()) !== 1) {
                return [];
            }

            $class = $classes->classes()->first();

            if (!$class instanceof ReflectionClass) {
                return [];
            }

            assert($class instanceof ReflectionClass);

            $interfaces = $class->interfaces();

            if (count($interfaces) !== 1) {
                return [];
            }

            if (count($class->methods()) > 0) {
                return [];
            }

            if ($class->parent()) {
                return [];
            }

            $interfaceFQN = (string) $interfaces->first()->type();

            return [
                CodeAction::fromArray([
                    'title' => sprintf('Decorate "%s"', $interfaceFQN),
                    'kind' => self::KIND,
                    'command' => new Command(
                        'Generate decorator',
                        GenerateDecoratorCommand::NAME,
                        [
                            $textDocument->uri,
                            $interfaceFQN,
                        ]
                    )
                ]),
            ];
        });
    }
}
