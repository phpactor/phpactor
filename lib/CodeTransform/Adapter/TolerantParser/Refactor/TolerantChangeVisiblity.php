<?php

namespace Phpactor\CodeTransform\Adapter\TolerantParser\Refactor;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\Core\AstProvider;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\CodeTransform\Domain\Refactor\ChangeVisiblity;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;

class TolerantChangeVisiblity implements ChangeVisiblity
{
    public function __construct(private AstProvider $parser = new \Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider())
    {
    }

    public function changeVisiblity(SourceCode $source, int $offset): SourceCode
    {
        $node = $this->parser->get((string) $source);
        $node = $node->getDescendantNodeAtPosition($offset);

        $node = $this->resolveMemberNode($node);

        if (null === $node) {
            return $source;
        }

        /** @phpstan-ignore-next-line */
        $textEdit = $this->resolveNewVisiblityTextEdit($node);

        if (null === $textEdit) {
            return $source;
        }

        return $source->withSource(TextEdits::one($textEdit)->apply($source));
    }

    /**
     * @param MethodDeclaration|PropertyDeclaration|ClassConstDeclaration $node
     */
    private function resolveNewVisiblityTextEdit(Node $node): ?TextEdit
    {
        foreach ($node->modifiers as $modifier) {
            if ($modifier->kind === TokenKind::PublicKeyword) {
                return $this->visiblityTextEdit($modifier, 'protected');
            }

            if ($modifier->kind === TokenKind::ProtectedKeyword) {
                return $this->visiblityTextEdit($modifier, 'private');
            }

            if ($modifier->kind === TokenKind::PrivateKeyword) {
                return $this->visiblityTextEdit($modifier, 'public');
            }
        }

        return null;
    }

    private function visiblityTextEdit(Token $modifier, string $newVisiblity): TextEdit
    {
        return TextEdit::create($modifier->getStartPosition(), $modifier->getWidth(), $newVisiblity);
    }

    private function resolveMemberNode(Node $node): ?Node
    {
        if (!(
            $node instanceof MethodDeclaration ||
            $node instanceof PropertyDeclaration ||
            $node instanceof ClassConstDeclaration
        )) {
            $node = $node->getFirstAncestor(
                MethodDeclaration::class,
                PropertyDeclaration::class,
                ClassConstDeclaration::class
            );
        }
        return $node;
    }
}
