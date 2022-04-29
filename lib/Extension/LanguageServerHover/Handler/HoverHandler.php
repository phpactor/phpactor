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

            $offsetReflection = $this->reflector->reflectOffset($document, $offset);
            $symbolContext = $offsetReflection->symbolContext();
            $info = $this->infoFromReflecionOffset($offsetReflection);
            $string = new MarkupContent('markdown', $info);
            
            return new Hover($string, new Range(
                PositionConverter::byteOffsetToPosition(
                    ByteOffset::fromInt($symbolContext->symbol()->position()->start()),
                    $document->__toString()
                ),
                PositionConverter::byteOffsetToPosition(
                    ByteOffset::fromInt($symbolContext->symbol()->position()->end()),
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
        $symbolContext = $offset->symbolContext();

        if ($info = $this->infoFromSymbolContext($symbolContext)) {
            return $info;
        }

        return $this->renderer->render($offset);
    }

    private function infoFromSymbolContext(NodeContext $symbolContext): ?string
    {
        try {
            return $this->renderSymbolContext($symbolContext);
        } catch (CouldNotFormat $e) {
        }

        return null;
    }

    private function renderSymbolContext(NodeContext $symbolContext): ?string
    {
        switch ($symbolContext->symbol()->symbolType()) {
            case Symbol::METHOD:
            case Symbol::PROPERTY:
            case Symbol::CONSTANT:
                return $this->renderMember($symbolContext);
            case Symbol::CLASS_:
                return $this->renderClass($symbolContext->type());
            case Symbol::FUNCTION:
                return $this->renderFunction($symbolContext);
        }

        return null;
    }

    private function renderMember(NodeContext $symbolContext): string
    {
        $name = $symbolContext->symbol()->name();
        $container = $symbolContext->containerType();
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
                $symbolType = $symbolContext->symbol()->symbolType();
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

    private function renderFunction(NodeContext $symbolContext): string
    {
        $name = $symbolContext->symbol()->name();
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
}
