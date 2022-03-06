<?php

namespace Phpactor\Extension\Debug\Command;

use Phpactor\Extension\Debug\Model\JsonSchemaBuilder;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateJsonSchemaCommand extends Command
{
    /**
     * @var JsonSchemaBuilder
     */
    private $builder;

    public function __construct(JsonSchemaBuilder $builder)
    {
        parent::__construct();
        $this->builder = $builder;
    }

    protected function configure(): void
    {
        $this->setDescription('Dump the JSON schema to the given relative path');
        $this->addArgument('path', InputArgument::REQUIRED, 'Target path for JSON schema file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = (string)$input->getArgument('path');
        if (!@file_put_contents(
            $path,
            $this->builder->dump()
        )) {
            throw new RuntimeException(sprintf(
                'Could not write JSON file "%s"',
                $path
            ));
        }
        return 0;
    }
}
