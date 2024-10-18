<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Domain\Refactor\OverrideMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\Extension\LanguageServerCodeTransform\Model\OverrideMethod\OverridableMethodFinder;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use function Amp\call;

class OverrideMethodCommand implements Command
{
    public const NAME  = 'override_method';

    public function __construct(
        private ClientApi $clientApi,
        private OverrideMethod $overrideMethod,
        private OverridableMethodFinder $finder,
        private TextDocumentLocator $locator
    ) {
    }

    /**
     * @return Promise<?ApplyWorkspaceEditResult>
     */
    public function __invoke(string $uri): Promise
    {
        return call(function () use ($uri) {
            $document = $this->locator->get(TextDocumentUri::fromString($uri));
            $sourceCode = SourceCode::fromStringAndPath(
                $document->__toString(),
                $document->uriOrThrow()->__toString(),
            );

            $method = yield $this->resolveClassMethodName($document);

            if (null === $method) {
                return null;
            }

            [$className, $methodName] = [ $method->class()->name()->__toString(), $method->name()];

            $textEdits = null;
            try {
                $textEdits = $this->overrideMethod->overrideMethod($sourceCode, $className, $methodName);
            } catch (TransformException $error) {
                $this->clientApi->window()->showMessage()->warning($error->getMessage());
                return new Success(null);
            } catch (NotFound $error) {
                $this->clientApi->window()->showMessage()->warning($error->getMessage());
                return new Success(null);
            }

            return $this->clientApi->workspace()->applyEdit(
                new WorkspaceEdit([
                    $uri => TextEditConverter::toLspTextEdits(
                        $textEdits,
                        $document->__toString(),
                    )
                ]),
                'Override method'
            );
        });
    }

    /**
     * @return Promise<?ReflectionMethod>
     */
    private function resolveClassMethodName(TextDocument $document): Promise
    {
        return call(function () use ($document) {
            $methods = $this->finder->find($document);
            usort($methods, function (ReflectionMethod $a, ReflectionMethod $b) {
                return $a->name() <=> $b->name();
            });

            $choice = yield $this->clientApi->window()->showMessageRequest()->info('Choose method:', ...array_map(
                fn (ReflectionMethod $method) => new MessageActionItem($this->formatName($method)),
                $methods
            ));


            foreach ($methods as $method) {
                if ($this->formatName($method) === $choice->title) {
                    return $method;
                }
            }

            return null;
        });
    }

    private function formatName(ReflectionMethod $method): string
    {
        return sprintf(
            '%s%s%s',
            $method->class()->name()->short(),
            $method->isStatic() ? '::' : '->',
            $method->name()
        );
    }
}
