<?php

namespace Phpactor\Extension\LanguageServerHover\Handler;

use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\Hover;
use Phpactor\LanguageServerProtocol\MarkupContent;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\Completion\Core\Exception\CouldNotFormat;
use Phpactor\Extension\LanguageServerHover\Renderer\HoverInformation;
use Phpactor\Extension\LanguageServerHover\Renderer\MemberDocblock;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\ObjectRenderer\Model\ObjectRenderer;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Reflector;

class HoverHandler implements Handler, CanRegisterCapabilities
{
    private Reflector $reflector;

    private ObjectRenderer $renderer;

    private Workspace $workspace;

    public function __construct(Workspace $workspace, Reflector $reflector, ObjectRenderer $renderer)
    {
        $this->reflector = $reflector;
        $this->renderer = $renderer;
        $this->workspace = $workspace;
    }

    public function methods(): array
    {
        return [
            'textDocument/hover' => 'hover',
        ];
    }

    /**
     * @return Promise<Hover|null>
     */
    public function hover(
        TextDocumentIdentifier $textDocument,
        Position $position
    ): Promise {
        return \Amp\call(function () use ($textDocument, $position) {
            $document = $this->workspace->get($textDocument->uri);
            $offset = PositionConverter::positionToByteOffset($position, $document->text);
            $document = TextDocumentBuilder::create($document->text)
                ->uri($document->uri)
                ->language('php')
                ->build();

            $char = substr($document, $offset->toInt(), 1);

            // do not provide hover for whitespace
            if (trim($char) == '') {
                return null;
            }

            $offsetReflection = $this->reflector->reflectOffset($document, $offset);
            $info = $this->infoFromReflecionOffset($offsetReflection);
            $string = new MarkupContent('markdown', $info);
            $nodeContext = $offsetReflection->symbolContext();

            return new Hover($string, new Range(
                PositionConverter::byteOffsetToPosition(
                    ByteOffset::fromInt($nodeContext->symbol()->position()->start()),
                    $document->__toString()
                ),
                PositionConverter::byteOffsetToPosition(
                    ByteOffset::fromInt($nodeContext->symbol()->position()->end()),
                    $document->__toString()
                )
            ));
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->hoverProvider = true;
    }

    private function infoFromReflecionOffset(ReflectionOffset $offset): string
    {
        $nodeContext = $offset->symbolContext();

        if ($info = $this->infoFromSymbolContext($nodeContext)) {
            return $info;
        }

        return $this->renderer->render($offset);
    }

    private function infoFromSymbolContext(NodeContext $nodeContext): ?string
    {
        try {
            return $this->renderSymbolContext($nodeContext);
        } catch (CouldNotFormat $e) {
        }

        return null;
    }

    private function renderSymbolContext(NodeContext $nodeContext): ?string
    {
        switch ($nodeContext->symbol()->symbolType()) {
            case Symbol::METHOD:
            case Symbol::PROPERTY:
            case Symbol::DECLARED_CONSTANT:
                return $this->renderDeclaredConstant($nodeContext);
            case Symbol::CONSTANT:
                return $this->renderMember($nodeContext);
            case Symbol::CLASS_:
                return $this->renderClass($nodeContext->type());
            case Symbol::FUNCTION:
                return $this->renderFunction($nodeContext);
        }

        return null;
    }

    private function renderMember(NodeContext $nodeContext): string
    {
        $name = $nodeContext->symbol()->name();
        $container = $nodeContext->containerType();
        $infos = [];

        foreach ($container->classNamedTypes() as $namedType) {
            try {
                $class = $this->reflector->reflectClassLike((string) $namedType);
                $member = null;
                $sep = '#';

                // note that all class-likes (classes, traits and interfaces) have
                // methods but not all have constants or properties, so we play safe
                // with members() which is first-come-first-serve, rather than risk
                // a fatal error because of a non-existing method.
                $symbolType = $nodeContext->symbol()->symbolType();
                switch ($symbolType) {
                    case Symbol::METHOD:
                        $member = $class->methods()->get($name);
                        $sep = '#';
                        break;
                    case Symbol::CONSTANT:
                        $sep = '::';
                        $member = $class->members()->get($name);
                        break;
                    case Symbol::PROPERTY:
                        $sep = '$';
                        $member = $class->members()->get($name);
                        break;
                    default:
                        return sprintf('Unknown symbol type "%s"', $symbolType);
                }

                $infos[] = $this->renderer->render(new HoverInformation(
                    $namedType->short() .' '.$sep.' '.(string)$member->name(),
                    $this->renderer->render(
                        new MemberDocblock($member)
                    ),
                    $member
                ));
            } catch (NotFound $e) {
                continue;
            }
        }

        return implode("\n", $infos);
    }

    private function renderFunction(NodeContext $nodeContext): string
    {
        $name = $nodeContext->symbol()->name();
        try {
            $function = $this->reflector->reflectFunction($name);
        } catch (NotFound $notFound) {
            return $notFound->getMessage();
        }

        return $this->renderer->render(new HoverInformation($name, $function->docblock()->formatted(), $function));
    }

    private function renderClass(Type $type): string
    {
        try {
            $class = $this->reflector->reflectClassLike((string) $type);
            return $this->renderer->render(new HoverInformation(
                $type->short(),
                $class->docblock()->formatted(),
                $class
            ));
        } catch (NotFound $e) {
            return $e->getMessage();
        }
    }

    private function renderDeclaredConstant(NodeContext $context): ?string
    {
        try {
            $constant = $this->reflector->reflectConstant($context->symbol()->name());
            return $this->renderer->render(new HoverInformation(
                $context->symbol()->name(),
                $constant->docblock()->formatted(),
                $constant
            ));
        } catch (NotFound $e) {
            return $e->getMessage();
        }
    }
}
