<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\ClassToFile;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
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

    public function __construct(
        private SourceCodeReflector $reflector,
        private Generators $generators,
        private ClassToFile $classToFile
    ) {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $range) {
            $diagnostics = (yield $this->reflector->diagnostics(TextDocumentConverter::fromLspTextItem($textDocument)))->byClass(
                UnresolvableNameDiagnostic::class
            )->containingRange(
                RangeConverter::toPhpactorRange($range, $textDocument->text)
            );
            $actions = [];

            foreach ($diagnostics as $diagnostic) {
                assert($diagnostic instanceof UnresolvableNameDiagnostic);
                if ($diagnostic->type() !== UnresolvableNameDiagnostic::TYPE_CLASS) {
                    continue;
                }

                foreach ($this->classToFile->classToFileCandidates(ClassName::fromString($diagnostic->name())) as $candidate) {
                    assert($candidate instanceof FilePath);
                    foreach ($this->generators as $name => $_) {
                        $title = sprintf('Create %s file for "%s"', $name, $diagnostic->name()->__toString());
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
                                    TextDocumentUri::fromString($candidate->__toString())->__toString(),
                                    $name
                                ]
                            )
                        ]);
                    }
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

    public function describe(): string
    {
        return 'create class for any class which cannot be found';
    }
}
