<?php

namespace Phpactor\Extension\Laravel\Handler;

use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\LanguageServerProtocol\DidSaveTextDocumentNotification;
use Phpactor\LanguageServerProtocol\DidSaveTextDocumentParams;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Reflector;

class RefreshOnLaravelFileUpdateHandler implements Handler
{
    public function __construct(
        private LaravelContainerInspector $laravelContainer,
        private Workspace $workspace,
        private Reflector $reflector,
    ) {
    }

    public function methods(): array
    {
        return [
            DidSaveTextDocumentNotification::METHOD => 'refreshCacheIfNeeded',
        ];
    }

    public function refreshCacheIfNeeded(DidSaveTextDocumentParams $params): void
    {
        $textDocument = $this->workspace->get($params->textDocument->uri);

        $source = TextDocumentBuilder::create($textDocument->text)
            ->language($textDocument->languageId)
            ->build();

        $classes = $this->reflector->reflectClassesIn($source);

        if ($classes->count() !== 1) {
            return;
        }

        $class = $classes->first();

        if ($class->isInstanceOf(ClassName::fromString('Illuminate\Database\Eloquent\Model'))) {
            $this->laravelContainer->modelChanged($class);
            return;
        }
        if ($class->isInstanceOf(ClassName::fromString('Livewire\Component'))) {
            $this->laravelContainer->livewireComponentChanged($class);
            return;
        }
        if ($class->isInstanceOf(ClassName::fromString('Illuminate\View\Component'))) {
            $this->laravelContainer->bladeComponentChanged($class);
            return;
        }
    }
}
