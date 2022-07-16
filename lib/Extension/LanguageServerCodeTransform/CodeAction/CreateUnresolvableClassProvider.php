<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\ClassToFile;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\CreateClassCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnresolvableNameDiagnostic;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use function Amp\call;

class CreateUnresolvableClassProvider implements CodeActionProvider
{
    public const KIND = 'quickfix.create_unresolable_class';

    private SourceCodeReflector $reflector;

    private ClassToFile $classToFile;

    public function __construct(SourceCodeReflector $reflector, ClassToFile $classToFile)
    {
        $this->reflector = $reflector;
        $this->classToFile = $classToFile;
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument) {
            $diagnostics = $this->reflector->diagnostics($textDocument->text)->byClass(UnresolvableNameDiagnostic::class);
            $actions = [];
            foreach ($diagnostics as $diagnostic) {
                assert($diagnostic instanceof UnresolvableNameDiagnostic);
                if ($diagnostic->type() !== UnresolvableNameDiagnostic::TYPE_CLASS) {
                    continue;
                }

                $title = sprintf('Create class "%s"', $diagnostic->name()->__toString());
                foreach ($this->classToFile->classToFileCandidates(ClassName::fromString($diagnostic->name())) as $candidate) {
                    $actions[] = CodeAction::fromArray([
                        'title' =>  $title,
                        'kind' => self::KIND,
                        'diagnostics' => [
                            ProtocolFactory::diagnostic(RangeConverter::toLspRange($diagnostic->range(), $textDocument->text), $diagnostic->message())
                        ],
                        'command' => new Command(
                            $title,
                            CreateClassCommand::NAME,
                            [
                                TextDocumentUri::fromString((string)$candidate)->__toString(),
                                'default',
                            ]
                        )
                    ]);
                }
            }

            return $actions;
        });
    }

    public function kinds(): array
    {
        return [
            self::KIND
        ];
    }
}
