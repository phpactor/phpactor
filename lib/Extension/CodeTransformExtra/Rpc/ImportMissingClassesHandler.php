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
use function Amp\Promise\wait;

class ImportMissingClassesHandler implements Handler
{
    public const NAME = 'import_missing_classes';
    public const PARAM_SOURCE = 'source';
    public const PARAM_PATH = 'path';

    public function __construct(
        private readonly RequestHandler $handler,
        private readonly Reflector $reflector
    ) {
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

        $diagnostics = wait($this->reflector->diagnostics($document))->byClass(UnresolvableNameDiagnostic::class);

        $responses = [];
        foreach ($diagnostics as $unresolvedClass) {
            assert($unresolvedClass instanceof UnresolvableNameDiagnostic);

            $responses[] = $this->handler->handle(Request::fromNameAndParameters(ImportClassHandler::NAME, [
                ImportClassHandler::PARAM_PATH => $arguments[self::PARAM_PATH],
                ImportClassHandler::PARAM_SOURCE => $arguments[self::PARAM_SOURCE],
                ImportClassHandler::PARAM_OFFSET => $unresolvedClass->range()->start()->toInt() + 1
            ]));
        }

        if ($responses === []) {
            return EchoResponse::fromMessage('No unresolved classes found');
        }

        return CollectionResponse::fromActions($responses);
    }

    public function name(): string
    {
        return self::NAME;
    }
}
