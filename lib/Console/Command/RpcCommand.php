<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Rpc\RequestHandler;
use Phpactor\Rpc\Request;

class RpcCommand extends Command
{
    /**
     * @var RequestHandler
     */
    private $handler;

    public function __construct(RequestHandler $handler)
    {
        parent::__construct();
        $this->handler = $handler;
    }

    public function configure()
    {
        $this->setName('rpc');
        $this->setDescription('Execute one or many actions from stdin and receive an imperative response');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $stdin = $this->stdin();
        $request = json_decode($stdin, true);

        if (null === $request) {
            throw Response::fromActions(
                ErrorAction::fromMessageAndDetails('Could not decode JSON', $this->stdin())
            );
        }

        $request = Request::fromArray($request);
        $response = $this->handler->handle($request);

        $output->write(json_encode($response->toArray()));
    }

    private function stdin()
    {
        $in = '';

        while ($line = fgets(STDIN)) {
            $in .= $line;
        }

        return $in;
    }
}

