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
use Phpactor\Generation\SnippetGeneratorRegistry;

class GenerateSnippetCommand extends Command
{
    /**
     * @var SnippetGeneratorRegistry
     */
    private $registry;

    public function __construct(SnippetGeneratorRegistry $registry)
    {
        parent::__construct();
        $this->registry = $registry;
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
        $generator = $this->registry->get($input->getArgument('generator'));
        $snippet = $generator->generate($context);
        $output->write($snippet);
    }
}
