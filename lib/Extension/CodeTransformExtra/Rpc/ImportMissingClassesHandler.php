<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\Helper\UnresolvableClassNameFinder;
use Phpactor\CodeTransform\Domain\NameWithByteOffset;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\RequestHandler;
use Phpactor\Extension\Rpc\Response\CollectionResponse;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\MapResolver\Resolver;
use Phpactor\TextDocument\TextDocumentBuilder;

class ImportMissingClassesHandler implements Handler
{
    public const NAME = 'import_missing_classes';
    public const PARAM_SOURCE = 'source';
    public const PARAM_PATH = 'path';

    private UnresolvableClassNameFinder $unresolvableClassNameFinder;

    private RequestHandler $handler;

    public function __construct(
        RequestHandler $handler,
        UnresolvableClassNameFinder $unresolvableClassNameFinder
    ) {
        $this->unresolvableClassNameFinder = $unresolvableClassNameFinder;
        $this->handler = $handler;
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

        $unresolvedClasses = $this->unresolvableClassNameFinder->find($document)->onlyUniqueNames();

        $responses = [];
        foreach ($unresolvedClasses as $unresolvedClass) {
            assert($unresolvedClass instanceof NameWithByteOffset);
            $responses[] = $this->handler->handle(Request::fromNameAndParameters(ImportClassHandler::NAME, [
                ImportClassHandler::PARAM_PATH => $arguments[self::PARAM_PATH],
                ImportClassHandler::PARAM_SOURCE => $arguments[self::PARAM_SOURCE],
                ImportClassHandler::PARAM_OFFSET => $unresolvedClass->byteOffset()->toInt() + 1
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
