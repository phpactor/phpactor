<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\WorseReflection\Reflector;
use Phpactor\Application\Doctor;
use Symfony\Component\Console\Helper\Table;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;
use Phpactor\Console\Formatter\Highlight;
use Phpactor\Console\Dumper\DumperRegistry;
use Symfony\Component\Console\Input\InputArgument;

class DiagnoseCommand extends Command
{
    /**
     * @var Doctor
     */
    private $doctor;

    /**
     * @var DumperRegistry
     */
    private $dumperRegistry;

    public function __construct(
        DumperRegistry $dumperRegistry,
        Doctor $doctor
    )
    {
        parent::__construct();
        $this->doctor = $doctor;
        $this->dumperRegistry = $dumperRegistry;
    }

    protected function configure()
    {
        $this->setName('diagnose');
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to diagnose');
        Handler\FormatHandler::configure($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = $input->getOption('format');

        $problemsByFile = $this->doctor->diagnose($input->getArgument('path'));

        if ($format) {
            $this->dumperRegistry->get($format)->dump($output, $problemsByFile);
            return;
        }

        foreach ($problemsByFile as $path => $problems)
        {
            if (empty($problems)) {
                continue;
            }

            $output->writeln('<info>' . $path . '</>');
            $table = new Table($output);

            /** @var SymbolInformation $problem */
            foreach ($problems as $problem) {
                $table->addRow([
                    sprintf(
                        '<comment>%s</>:<comment>%s</>',
                        $problem['line_number'], 
                        $problem['column']
                    ),
                    $problem['problem']
                ]);
            }

            $table->render();
        }
    }
}
