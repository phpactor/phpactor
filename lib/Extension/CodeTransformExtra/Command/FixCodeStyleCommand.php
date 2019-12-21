<?php

namespace Phpactor\Extension\CodeTransformExtra\Command;

use Phpactor\CodeBuilder\Domain\StyleFixer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixCodeStyleCommand extends Command
{
    const ARG_PATH = 'path';

    /**
     * @var StyleFixer
     */
    private $fixer;

    public function __construct(StyleFixer $fixer)
    {
        $this->fixer = $fixer;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Apply Phpactor\'s CS fixer to a single file (experimental)');
        $this->setHelp(<<<'EOT'
Applies the Phpactor CS fixer to a file and dump the result to stdout.

This fixer is not intended to be used instead of tools such as php-cs-fixer or
phpcbf.

It is used internally to sanitize generated code, this command's purpose is to
help debug this process.
EOT
        );

        $this->addArgument(self::ARG_PATH, InputArgument::REQUIRED, 'Path to source file');

    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->fixer->fix(file_get_contents($input->getArgument(self::ARG_PATH))));
    }
}
