<?php

namespace Phpactor\Indexer\Extension\Command;

use Phpactor\Indexer\Model\RecordReference;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;
use Phpactor\Indexer\Model\Record\MemberRecord;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Cast\Cast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexQueryCommand extends Command
{
    const ARG_IDENITIFIER = 'identifier';

    public function __construct(private QueryClient $query)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(self::ARG_IDENITIFIER, InputArgument::REQUIRED, 'Query (function name, class name, <memberType>#<memberName>)');
        $this->setDescription(
            'Show the indexed information for a given identifier'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $class = $this->query->class()->get(Cast::toString($input->getArgument(self::ARG_IDENITIFIER)));

        if ($class) {
            $this->renderClass($output, $class);
        }

        $function = $this->query->function()->get(
            Cast::toString($input->getArgument(self::ARG_IDENITIFIER))
        );

        if ($function) {
            $this->renderFunction($output, $function);
        }

        $member = $this->query->member()->get(Cast::toString($input->getArgument(self::ARG_IDENITIFIER)));

        if ($member) {
            $this->renderMember($output, $member);
        }

        return 0;
    }

    private function renderClass(OutputInterface $output, ClassRecord $class): void
    {
        $output->writeln('<info>Class:</>'.$class->fqn());
        $output->writeln('<info>Path:</>'.$class->filePath());
        $output->writeln('<info>Type:</>'.$class->type());
        $output->writeln('<info>Implements</>:');
        foreach ($class->implements() as $fqn) {
            $output->writeln(' - ' . (string)$fqn);
        }
        $output->writeln('<info>Implementations</>:');
        foreach ($class->implementations() as $fqn) {
            $output->writeln(' - ' . (string)$fqn);
        }
        $output->writeln('<info>Referenced by</>:');
        foreach ($class->references() as $path) {
            $file = $this->query->file()->get($path);
            $output->writeln(sprintf('- %s:%s', $path, implode(', ', array_map(function (RecordReference $reference) {
                return $reference->start().'-'.$reference->end();
            }, $file->references()->to($class)->toArray()))));
        }
    }

    private function renderFunction(OutputInterface $output, FunctionRecord $function): void
    {
        $output->writeln('<info>Function:</>'.$function->fqn());
        $output->writeln('<info>Path:</>'.$function->filePath());
        $output->writeln('<info>Referenced by</>:');
        foreach ($function->references() as $path) {
            $file = $this->query->file()->get($path);
            $output->writeln(sprintf('- %s:%s', $path, implode(', ', array_map(function (RecordReference $reference) {
                return $reference->start().'-'.$reference->end();
            }, $file->references()->to($function)->toArray()))));
        }
    }

    private function renderMember(OutputInterface $output, MemberRecord $member): void
    {
        $output->writeln('<info>Member:</>'.$member->memberName());
        $output->writeln('<info>Member Type:</>'.$member->type()->value);
        $output->writeln('<info>Referenced by</>:');
        foreach ($this->query->member()->referencesTo($member->type(), $member->memberName()) as $index => $location) {
            $output->writeln(sprintf(
                '%-3d %s:%s-%s',
                $index + 1 . '.',
                $location->location()->uri()->path(),
                $location->location()->range()->start()->toInt(),
                $location->location()->range()->end()->toInt(),
            ));
        }
    }
}
