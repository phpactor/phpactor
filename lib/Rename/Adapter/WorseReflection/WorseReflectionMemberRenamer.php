<?php

namespace Phpactor\Rename\Adapter\WorseReflection;

use Generator;
use Phpactor\Rename\Model\LocatedTextEdit;
use Phpactor\Rename\Model\Renamer;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdit as PhpactorTextEdit;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type\ClassLikeType;
use Phpactor\WorseReflection\Reflector;

class WorseReflectionMemberRenamer implements Renamer
{
    public function __construct(
        private readonly Reflector $reflector,
    ) {
    }

    public function getRenameRange(TextDocument $textDocument, ByteOffset $offset): ?ByteOffsetRange
    {
        $member = $this->resolveMember($textDocument, $offset);

        if (null === $member) {
            return null;
        }

        return $member->nameRange();
    }

    public function rename(TextDocument $textDocument, ByteOffset $offset, string $newName): Generator
    {
        $member = $this->resolveMember($textDocument, $offset);

        if (null === $member) {
            return;
        }

        $uri = $member->class()->sourceCode()->uri();

        if (null === $uri) {
            return;
        }

        $rangeStart = $member->nameRange()->start();
        yield new LocatedTextEdit(
            $uri,
            PhpactorTextEdit::create(
                $rangeStart,
                $member->nameRange()->length(),
                $newName,
            )
        );

        $accesses = match ($member->memberType()) {
            ReflectionMember::TYPE_METHOD => $this->reflector->navigate($textDocument)->methodCalls(),
            ReflectionMember::TYPE_PROPERTY => $this->reflector->navigate($textDocument)->propertyAccesses(),
            default => [],
        };

        foreach ($accesses as $access) {
            if ($access->name() !== $member->name()) {
                continue;
            }
            yield new LocatedTextEdit(
                $uri,
                PhpactorTextEdit::create($access->nameRange()->start(), $access->nameRange()->length(), $newName)
            );
        }
    }

    private function resolveMember(TextDocument $textDocument, ByteOffset $offset): ?ReflectionMember
    {
        $context = $this->reflector->reflectOffset($textDocument, $offset)->nodeContext();

        $symbolType = $context->symbol()->symbolType();
        if (!in_array($symbolType, [
            Symbol::METHOD,
            Symbol::PROPERTY,
            Symbol::VARIABLE, // promoted properties 🙃
        ])) {
            return null;
        }

        $containerType = $context->containerType();

        if (!$containerType instanceof ClassLikeType) {
            return null;
        }

        $class = $this->reflector->reflectClassLike($containerType->name());

        $memberType = $symbolType === Symbol::VARIABLE ? 'property' : $symbolType;
        $members = $class->members()->byMemberType($memberType)->byName($context->symbol()->name());

        foreach ($members as $member) {
            if (!$member->visibility()->isPrivate()) {
                return null;
            }
            return $member;
        }

        return null;
    }
}
