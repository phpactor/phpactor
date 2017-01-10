<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Phpactor\Util\ClassUtil;
use BetterReflection\Reflector\ClassReflector;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Util\FileUtil;
use Phpactor\Generation\Snippet\ImplementMissingMethodsGenerator;
use Phpactor\Generation\SnippetCreator;

class GenerateSnippetCommand extends Command
{
    /**
     * @var SnippetCreator
     */
    private $creator;

    public function __construct(SnippetCreator $creator)
    {
        parent::__construct();
        $this->creator = $creator;
    }

    public function configure()
    {
        $this->setName('generate:snippet');
        $this->addArgument('generator', InputArgument::REQUIRED, 'Name of snippet generator');
        Handler\CodeContextHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $context = Handler\CodeContextHandler::contextFromInput($input);
        $snippet = $this->creator->create($context, $input->getArgument('generator'), []);
        $output->write($snippet);
    }
}
