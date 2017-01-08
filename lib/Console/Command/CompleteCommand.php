<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Reflection\ComposerReflector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Reflection\ReflectorInterface;
use Phpactor\Complete\Completer;
use Phpactor\Util\FileUtil;
use Phpactor\CodeContext;

class CompleteCommand extends Command
{
    /**
     * @var Completer
     */
    private $completer;

    public function __construct(
        Completer $completer
    )
    {
        parent::__construct();
        $this->completer = $completer;
    }

    public function configure()
    {
        $this->setName('complete');
        $this->setDescription('Explain a class by its class FQN or filename');
        Handler\CodeContextHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $context = Handler\CodeContextHandler::contextFromInput($input);
        $completions = $this->completer->complete($context);
        $output->writeln($out = json_encode($completions->all(), JSON_PRETTY_PRINT));
    }
}
