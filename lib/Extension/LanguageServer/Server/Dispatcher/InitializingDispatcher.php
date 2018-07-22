<?php

namespace Phpactor\Extension\LanguageServer\Server\Dispatcher;

use Phpactor\Extension\LanguageServer\Protocol\ResponseError;
use Phpactor\Extension\LanguageServer\Protocol\ResponseMessage;
use Phpactor\Extension\LanguageServer\Server\Dispatcher;
use Phpactor\Extension\LanguageServer\Server\Project;

class InitializingDispatcher implements Dispatcher
{
    private $initialized = false;

    /**
     * @var Dispatcher
     */
    private $innerDispatcher;

    /**
     * @var Project
     */
    private $project;

    public function __construct(Dispatcher $innerDispatcher, Project $project)
    {
        $this->innerDispatcher = $innerDispatcher;
        $this->project = $project;
    }

    public function dispatch(array $request): ResponseMessage
    {
        if ($request['method'] !== 'initialize' && false === $this->project->isInitialized($request)) {
            return new ResponseMessage(
                1,
                null,
                new ResponseError(
                    ResponseError::ServerNotInitialized,
                    'Server has not been initialized'
                )
            );
        }

        return $this->innerDispatcher->dispatch($request);
    }
}
