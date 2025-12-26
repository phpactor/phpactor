<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Amp\Promise;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\NamespaceUseGroupClause;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\Statement\NamespaceUseDeclaration;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnusedImportDiagnostic;
use Phpactor\WorseReflection\Reflector;
use function Amp\call;

class RemoveUnusedImportsTransformer implements Transformer
{
    /**
     * @var array<int, bool>
     */
    private array $fixed = [];

    public function __construct(
        private readonly Reflector $reflector,
        private readonly AstProvider $parser
    ) {
    }

    /**
        * @return Promise<TextEdits>
     */
    public function transform(SourceCode $code): Promise
    {
        return call(function () use ($code) {
            $rootNode = $this->parser->get($code);
            $edits = [];

            foreach ((yield $this->reflector->diagnostics($code))->byClass(UnusedImportDiagnostic::class) as $unusedImport) {
                $importNode = $rootNode->getDescendantNodeAtPosition($unusedImport->range()->start()->toInt());

                if (!$importNode instanceof QualifiedName) {
                    continue;
                }

                $list = $importNode->getFirstAncestor(NamespaceUseClause::class);

                if (!$list instanceof NamespaceUseClause) {
                    continue;
                }

                if ($list->groupClauses) {
                    if ($edit = $this->forGroupClause($importNode, $list)) {
                        $edits[] = $edit;
                    }
                    continue;
                }

                // there is exactly one element
                $declaration = $importNode->getFirstAncestor(NamespaceUseDeclaration::class);
                if (null === $declaration) {
                    continue;
                }
                $length = $declaration->getEndPosition() - $declaration->getStartPosition();

                if (substr($code->__toString(), $declaration->getEndPosition(), 1) === "\n") {
                    $length++;
                }

                $edits[] = TextEdit::create(
                    $declaration->getStartPosition(),
                    $length,
                    ''
                );
            }

            return TextEdits::fromTextEdits($edits);
        });
    }

    /**
        * @return Promise<Diagnostics>
     */
    public function diagnostics(SourceCode $code): Promise
    {
        return call(function () use ($code) {
            $diagnostics = [];
            foreach ((yield $this->reflector->diagnostics($code))->byClass(UnusedImportDiagnostic::class) as $unusedClass) {
                $diagnostics[] = new Diagnostic(
                    $unusedClass->range(),
                    $unusedClass->message(),
                    Diagnostic::WARNING
                );
            }

            return new Diagnostics($diagnostics);
        });
    }

    private function forGroupClause(QualifiedName $importNode, NamespaceUseClause $list): ?TextEdit
    {
        $fixed = spl_object_id($list);
        if (isset($this->fixed[$fixed])) {
            return null;
        }
        $this->fixed[$fixed] = true;

        $names = [];
        foreach ($list->groupClauses?->children ?: [] as $groupClause) {
            if (!$groupClause instanceof NamespaceUseGroupClause) {
                continue;
            }

            if ($groupClause->namespaceName->__toString() === $importNode->__toString()) {
                continue;
            }
            $names[] = $groupClause->__toString();
        }

        $groupClauses = $list->groupClauses;

        if (null === $groupClauses) {
            return null;
        }

        return TextEdit::create(
            $groupClauses->getStartPosition(),
            $groupClauses->getEndPosition() - $groupClauses->getStartPosition(),
            implode(', ', $names)
        );
    }
}
