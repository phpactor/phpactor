<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\RequestHandler;
use Phpactor\Extension\Rpc\Response\CollectionResponse;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\MapResolver\Resolver;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnresolvableNameDiagnostic;
use Phpactor\WorseReflection\Reflector;

class ImportMissingClassesHandler implements Handler
{
    public const NAME = 'import_missing_classes';
    public const PARAM_SOURCE = 'source';
    public const PARAM_PATH = 'path';

    private RequestHandler $handler;

    private Reflector $reflector;

    public function __construct(
        RequestHandler $handler,
        Reflector $reflector
    ) {
        $this->handler = $handler;
        $this->reflector = $reflector;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setRequired([
            self::PARAM_PATH,
            self::PARAM_SOURCE,
        ]);
    }

    public function handle(array $arguments)
    {
        $document = TextDocumentBuilder::create(
            $arguments[self::PARAM_SOURCE]
        )->language('php')->uri($arguments[self::PARAM_PATH])->build();

        $diagnostics = $this->reflector->diagnostics($arguments[self::PARAM_SOURCE])->byClass(UnresolvableNameDiagnostic::class);

        $responses = [];
        foreach ($diagnostics as $unresolvedClass) {
            assert($unresolvedClass instanceof UnresolvableNameDiagnostic);

            $responses[] = $this->handler->handle(Request::fromNameAndParameters(ImportClassHandler::NAME, [
                ImportClassHandler::PARAM_PATH => $arguments[self::PARAM_PATH],
                ImportClassHandler::PARAM_SOURCE => $arguments[self::PARAM_SOURCE],
                ImportClassHandler::PARAM_OFFSET => $unresolvedClass->range()->start()->toInt() + 1
            ]));
        }

        if (empty($responses)) {
            return EchoResponse::fromMessage('No unresolved classes found');
        }

        return CollectionResponse::fromActions($responses);
    }

    public function name(): string
    {
        return self::NAME;
    }
}
