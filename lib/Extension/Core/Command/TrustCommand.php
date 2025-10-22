<?php

namespace Phpactor\Extension\Core\Command;

use Phpactor\Extension\Core\Trust\Trust;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class TrustCommand extends Command
{
    const OPT_TRUST = 'trust';

    public function __construct(private Trust $status, private string $projectDir)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Trust the current working directory and load the Phactor configuration');
        $this->addOption(self::OPT_TRUST, null, InputOption::VALUE_NONE, 'Trust and don\'t ask');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $trustIt = (bool)$input->getOption(self::OPT_TRUST);

        $message = match ($this->status->isTrusted($this->projectDir)) {
            true => sprintf('Path <fg=cyan>%s</> is <fg=green>trusted</> and configuartion will be loaded from it', $this->projectDir),
            false => sprintf('Path <fg=cyan>%s</> is <fg=yellow>not trusted</> configuration will not be loaded from it', $this->projectDir),
        };
        $output->writeln($message);

        $helper = new QuestionHelper();
        $yes = 'Yes. It\'s mine. I trust it';
        $no = 'No. I do not trust it';

        if (true == $trustIt) {
            $trusted = true;
        } else {
            $response = $helper->ask(
                $input,
                $output,
                new ChoiceQuestion(sprintf('Do you trust %s?', $this->projectDir), [
                    'yes' => $yes,
                    'no' => $no,
                ])
            );
            $trusted = $response === 'yes';
        }

        $this->status->setTrusted($this->projectDir, $trusted);

        if ($trusted) {
            $output->writeln(sprintf('%s is now <fg=green>trusted</>', $this->projectDir));
            return 0;
        }
        $output->writeln(sprintf('%s is now <fg=red>not trusted</>', $this->projectDir));

        return 0;
    }
}
