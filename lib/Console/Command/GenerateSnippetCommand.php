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
use Symfony\Component\Console\Input\InputOption;

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
        $this->addOption('options', null, InputOption::VALUE_REQUIRED, 'JSON encoded string of options', []);
        Handler\CodeContextHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOption('options');
        $context = Handler\CodeContextHandler::contextFromInput($input);

        if ($options) {
            $decoded = json_decode($options, true);

            if (false === $decoded) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not decode JSON option string "%s"', $options
                ));
            }

            $options = $decoded;
        }

        $snippet = $this->creator->create($context, $input->getArgument('generator'), $options);
        $output->write($snippet);
    }
}
