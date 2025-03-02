<?php

namespace Phpactor\Extension\Symfony\Model;

use Amp\CancellationToken;
use Amp\Promise;
use Generator;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Parser;
use PhpParser\Node\Expr\MethodCall;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\Symfony\Command\SymfonyCreateTemplateCommand;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;
use RecursiveDirectoryIterator;
use SplFileInfo;
use RecursiveIteratorIterator;
use function Amp\call;

final class SymfonyTemplateCache implements CodeActionProvider
{
    public const TEMPLATE_FOLDER = 'templates';
    public const TWIG_FILE_EXTENSION = 'twig';
    public const KIND = 'symfony.template.create';
    private const METHODS = [
        'render',
        'renderView',
        'renderBlock',
        'renderBlockView',
    ];
    private const ABSTRACT_CONTROLLER = 'Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController';

    /**
    * @var array<string, SymfonyTemplate>
    */
    private array $templates = [];

    public function __construct(
        private Reflector $reflector,
    ) {
        foreach (self::findFiles(self::TEMPLATE_FOLDER, self::TWIG_FILE_EXTENSION) as $path) {
            $this->templates[$path] = new SymfonyTemplate(
                $path,
                [],
            );
        }
    }

    /**
     * @return Generator<Suggestion>
     */
    public function completePath(): Generator
    {
        foreach ($this->templates as $template) {
            yield Suggestion::createWithOptions(
                $template->path,
                [
                    'label' => $template->path,
                    'short_description' => '',
                    'documentation' => '',
                    'type' => Suggestion::TYPE_FILE,
                    'priority' => 555,
                ]
            );
        }
    }

    public function addTemplate(string $template): void
    {
        $this->templates[$template] = new SymfonyTemplate($template, []);
    }

    public function provideActionsFor(
        TextDocumentItem $textDocument,
        Range $range,
        CancellationToken $cancel
    ): Promise {
        return call(function () use ($textDocument, $range) {

            $sourceCode = SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri);
            $offsetStart = PositionConverter::positionToByteOffset($range->start, $textDocument->text)->toInt();
            $offsetEnd = PositionConverter::positionToByteOffset($range->end, $textDocument->text)->toInt();

            // if($offsetStart === $offsetEnd) {
            //     return [];
            // }

            $parser = new Parser();
            $rootNode = $parser->parseSourceFile($textDocument->text);

            $node = $rootNode->getDescendantNodeAtPosition($offsetStart);

            if (!($node instanceof StringLiteral)) {
                return [];
            }

            $callNode = $node->parent?->parent?->parent;

            if (!($callNode instanceof CallExpression)) {
                return [];
            }

            $memberAccess = $callNode->callableExpression;

            if (!$memberAccess instanceof MemberAccessExpression) {
                return [];
            }

            $methodName = NodeUtil::nameFromTokenOrNode($callNode, $memberAccess->memberName);

            if (!in_array($methodName, self::METHODS, true)) {
                return [];
            }

            $expression = $memberAccess->dereferencableExpression;
            $containerType = $this->reflector->reflectOffset($sourceCode, $expression->getEndPosition())->nodeContext()->type();

            if ($containerType->instanceof(TypeFactory::class(self::ABSTRACT_CONTROLLER))->isFalseOrMaybe()) {
                return [];
            }

            $templateName = trim($node->getText(), '\'');

            if (array_key_exists($templateName, $this->templates)) {
                return [];
            }

            $nodeRange = ByteOffsetRange::fromInts($node->getStartPosition(), $node->getEndPosition());

            $diagnostic = new Diagnostic(
                range: RangeConverter::toLspRange($nodeRange, $textDocument->text),
                message: sprintf('Template "%s" does not exist', $templateName),
                severity: DiagnosticSeverity::WARNING,
                source: 'phpactor',
            );

            return [
                CodeAction::fromArray([
                    'title' => sprintf('Create template %s', $templateName),
                    'kind' => self::KIND,
                    'diagnostics' => [$diagnostic],
                    'command' => new Command(
                        'Create the template',
                        SymfonyCreateTemplateCommand::NAME,
                        [
                            $templateName,
                        ]
                    )
                ])
            ];
        });
    }

    public function kinds(): array
    {
        return [
            self::KIND,
        ];
    }

    public function describe(): string
    {
        return 'Create a new template';
    }

    public function name(): string
    {
        return 'symfony-create-template';
    }

    /**
     * @return Generator<string>
     */
    private static function findFiles(string $path, string $filetype): Generator
    {
        if (!is_dir($path)) {
            return true;
        }

        $directoryInfo = new SplFileInfo($path);
        $directoryIterator = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directoryIterator);

        /**
        * @var SplFileInfo $file filename
        */
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getExtension(), $filetype)) {
                $path = $file->getPathname();
                if (str_starts_with($path, $directoryInfo->getPathname())) {
                    $path = substr($path, strlen($directoryInfo->getPathname()));
                }

                yield ltrim($path, '/');
            }
        }

        return '';
    }
}
