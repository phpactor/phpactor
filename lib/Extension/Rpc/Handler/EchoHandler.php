<?php

namespace Phpactor\Extension\Rpc\Handler;

use Phpactor\Container\Schema;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\EchoResponse;

class EchoHandler implements Handler
{
    public function name(): string
    {
        return 'echo';
    }

    public function configure(Schema $schema): void
    {
        $schema->setDefaults([
            'message' => '',
        ]);
    }

    public function handle(array $arguments)
    {
        return EchoResponse::fromMessage($arguments['message']);
    }
}
