<?php

namespace Phpactor\Extension\Rpc\Command;

use Phpactor\Extension\Rpc\RpcVersion;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Extension\Rpc\RequestHandler;
use Phpactor\Extension\Rpc\Request;
use Symfony\Component\Console\Input\InputOption;
use RuntimeException;
use InvalidArgumentException;

class RpcCommand extends Command
{
    private RequestHandler $handler;

    private bool $storeReplay;

    private string $replayPath;

    /**
     * @var resource
     */
    private $inputStream;

    public function __construct(
        RequestHandler $handler,
        string $replayPath,
        bool $storeReplay = false,
        $inputStream = STDIN
    ) {
        parent::__construct();
        $this->handler = $handler;
        $this->storeReplay = $storeReplay;
        $this->replayPath = $replayPath;
        $this->inputStream = $inputStream;
    }

    public function configure(): void
    {
        $this->setDescription('Execute one or many actions from stdin and receive an imperative response');
        $this->addOption('replay', null, InputOption::VALUE_NONE, 'Replay the last request');
        $this->addOption('pretty', null, InputOption::VALUE_NONE, 'Pretty print JSON');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $stdin = $this->resolveInput((bool) $input->getOption('replay'));
        $request = json_decode($stdin, true);

        if (null === $request) {
            throw new InvalidArgumentException(sprintf(
                'Could not decode JSON: %s',
                $stdin
            ));
        }

        $response = $this->processRequest($request);
        $flags = 0;

        if ($input->getOption('pretty')) {
            $flags = JSON_PRETTY_PRINT;
        }

        $output->write((string) json_encode([
            'version' => RpcVersion::asString(),
            'action' => $response->name(),
            'parameters' => $response->parameters(),
        ], $flags), false, OutputInterface::OUTPUT_RAW);

        return 0;
    }

    private function processRequest(array $request)
    {
        $request = Request::fromArray($request);

        return $this->handler->handle($request);
    }

    private function resolveInput(bool $replay): string
    {
        if ($replay) {
            if (false === $this->storeReplay) {
                throw new RuntimeException(
                    'You must explicitly enable replay, set `rpc.store_replay` to `true` in your config.'
                );
            }
            return $this->lastRequest();
        }

        return $this->stdin();
    }

    private function stdin(): string
    {
        $in = '';

        while ($line = fgets($this->inputStream)) {
            $in .= $line;
        }

        if ($this->storeReplay) {
            $this->storeReplay($in);
        }

        return $in;
    }

    private function lastRequest()
    {
        $path = $this->replayPath;
        if (false === file_exists($path)) {
            throw new RuntimeException(sprintf(
                'Replace file does not exist at "%s"',
                $path
            ));
        }

        return file_get_contents($path);
    }

    private function storeReplay(string $in): void
    {
        $path = $this->replayPath;

        if (false === file_exists(dirname($this->replayPath))) {
            mkdir(dirname($path), 0700, true);
        }

        file_put_contents($path, $in);
        chmod($path, 0700);
    }
}
